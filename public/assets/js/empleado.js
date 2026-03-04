// empleado.js

let ultimoTipo = "ninguno"; // Inicializado desde DB
let ultimoFichajeSegundos = 0;
let cronometroInterval = null;

let btnEntrada = document.getElementById('btn-entrada');
let btnPausa = document.getElementById('btn-pausa');
let btnSalida = document.getElementById('btn-salida');
let timerSpan = document.getElementById('timer');

// ---------- FUNCIONES PRINCIPALES ----------
// Actualiza el estado de los botones según el último tipo
function actualizarBotones() {
    btnEntrada.disabled = !(ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnPausa.disabled = (ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnSalida.disabled = !(ultimoTipo === 'entrada' || ultimoTipo === 'fin_descanso');
    btnPausa.querySelector('span').textContent = (ultimoTipo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso';
}

// Inicia el cronómetro según el último fichaje
function iniciarCronometro() {
    if (cronometroInterval) clearInterval(cronometroInterval);

    if(['entrada','fin_descanso'].includes(ultimoTipo)){
        cronometroInterval = setInterval(async () => {
            ultimoFichajeSegundos++;
            timerSpan.textContent = formatTiempo(ultimoFichajeSegundos);
            await actualizarEstadoUsuario(); // Guardar cada segundo en DB
        }, 1000);
    } else {
        timerSpan.textContent = formatTiempo(ultimoFichajeSegundos);
    }
}

// Formatea segundos a HH:MM:SS
function formatTiempo(segundos) {
    let h = Math.floor(segundos / 3600).toString().padStart(2,'0');
    let m = Math.floor((segundos % 3600) / 60).toString().padStart(2,'0');
    let s = (segundos % 60).toString().padStart(2,'0');
    return `${h}:${m}:${s}`;
}

// Carga el estado del usuario desde la DB
async function cargarEstadoUsuario() {
    try {
        let response = await fetch('../app/controllers/EstadoController.php?action=getEstado', {
            method: 'GET',
            credentials: 'same-origin'
        });
        let data = await response.json();
        if(data.status === 'success') {
            ultimoTipo = data.tipo || "ninguno";
            ultimoFichajeSegundos = parseInt(data.segundos) || 0;
            actualizarBotones();
            iniciarCronometro();
        } else {
            console.error("No se pudo cargar estado:", data.message);
        }
    } catch(err) {
        console.error("Error cargando estado usuario:", err);
    }
}

// Ejecuta un fichaje y actualiza la DB
async function ejecutarFichaje(tipo) {
    let url = "../app/controllers/FichajeController.php?action=registrar";
    let horaCliente = new Date().toISOString();

    try {
        let response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'tipo': tipo, 'hora_cliente': horaCliente })
        });
        let data = await response.json();

        if(data.status === 'success'){
            Swal.fire({
                icon: 'success',
                title: '¡Hecho!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });

            // Actualizar tipo y segundos
            if(tipo === 'entrada') {
                ultimoFichajeSegundos = 0; // inicio jornada
            } else if(tipo === 'salida') {
                ultimoFichajeSegundos = 0; // reset al salir
            }

            ultimoTipo = tipo;

            // Guardar estado en DB
            await actualizarEstadoUsuario();
            setTimeout(() => location.reload(), 300);

            // Reiniciar cronómetro
            iniciarCronometro();
            actualizarBotones();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
}

// Actualiza el estado del usuario en DB (tipo + segundos)
async function actualizarEstadoUsuario() {
    try {
        await fetch('../app/controllers/EstadoController.php?action=updateEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'tipo': ultimoTipo,
                'segundos': ultimoFichajeSegundos
            }),
            credentials: 'same-origin'
        });
    } catch(err) {
        console.error("Error actualizando estado usuario:", err);
    }
}

//Logout 
function logoutBtn() {
    let logOutBtn = document.getElementById('btnLogout');
    if (!logOutBtn) return;

    logOutBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        let result = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Se cerrará tu sesión actual",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                let response = await fetch('/app/controllers/AuthController.php?action=logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                let data = await response.json();
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Sesión cerrada', text: data.message, timer: 1000, showConfirmButton: false });
                    setTimeout(() => { window.location.href = '/public/index.php'; }, 500);
                } else {
                    Swal.fire('Error', 'No se pudo cerrar sesión', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            }
        }
    });
}

//Paginacion
let recordsPerPage = 5;
let currentPage = 1;
let historial = window.historialEmpleado || [];

function renderTablePage(page = 1) {
    let tbody = document.querySelector(".admin-table tbody");
    if (!tbody) return;

    let start = (page - 1) * recordsPerPage;
    let end = start + recordsPerPage;
    let pageRecords = historial.slice(start, end);

    tbody.innerHTML = "";

    if (pageRecords.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2">No hay registros recientes.</td></tr>`;
        return;
    }

    pageRecords.forEach(reg => {
        tbody.innerHTML += `
            <tr>
                <td>${new Date(reg.fecha_hora).toLocaleString()}</td>
                <td><span class="tag-active">${reg.tipo.replace('_',' ').toUpperCase()}</span></td>
            </tr>
        `;
    });

    document.getElementById("currentPage").textContent = page;
    document.getElementById("prevPage").disabled = page === 1;
    document.getElementById("nextPage").disabled = end >= historial.length;
}

document.getElementById("prevPage").addEventListener("click", () => { 
    if(currentPage > 1){ currentPage--; renderTablePage(currentPage); } 
});
document.getElementById("nextPage").addEventListener("click", () => { 
    if(currentPage*recordsPerPage < historial.length){ currentPage++; renderTablePage(currentPage); } 
});

//Eventos
btnEntrada.addEventListener('click', () => ejecutarFichaje('entrada'));
btnSalida.addEventListener('click', () => ejecutarFichaje('salida'));
btnPausa.addEventListener('click', () => {
    let accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
    ejecutarFichaje(accion);
});

//Iniciar
logoutBtn();
cargarEstadoUsuario();
renderTablePage(currentPage);