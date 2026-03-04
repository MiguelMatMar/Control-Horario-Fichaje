//empleado.js

let ultimoTipo = window.ultimoTipoGlobal || "ninguno"; // Inicializado desde PHP
let ultimoFichajeSegundos = 0;
let cronometroInterval = null;

let btnEntrada = document.getElementById('btn-entrada');
let btnPausa = document.getElementById('btn-pausa');
let btnSalida = document.getElementById('btn-salida');
let timerSpan = document.getElementById('timer');

function actualizarBotones() {
    btnEntrada.disabled = !(ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnPausa.disabled = (ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnSalida.disabled = !(ultimoTipo === 'entrada' || ultimoTipo === 'fin_descanso');
    btnPausa.querySelector('span').textContent = (ultimoTipo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso';
}

function iniciarCronometro() {
    // Detener interval si existe
    if(cronometroInterval) clearInterval(cronometroInterval);

    // Restaurar tiempo desde sessionStorage si existe
    let tiempoGuardado = sessionStorage.getItem('ultimoFichajeSegundos');
    if(tiempoGuardado) ultimoFichajeSegundos = parseInt(tiempoGuardado, 10);

    if(['entrada','fin_descanso'].includes(ultimoTipo)){
        cronometroInterval = setInterval(() => {
            ultimoFichajeSegundos++;
            sessionStorage.setItem('ultimoFichajeSegundos', ultimoFichajeSegundos); // Guardar tiempo
            let h = Math.floor(ultimoFichajeSegundos / 3600).toString().padStart(2,'0');
            let m = Math.floor((ultimoFichajeSegundos % 3600) / 60).toString().padStart(2,'0');
            let s = (ultimoFichajeSegundos % 60).toString().padStart(2,'0');
            timerSpan.textContent = `${h}:${m}:${s}`;
        }, 1000);
    } else {
        // Si no estamos trabajando (ej. descanso o fuera de jornada)
        let h = Math.floor(ultimoFichajeSegundos / 3600).toString().padStart(2,'0');
        let m = Math.floor((ultimoFichajeSegundos % 3600) / 60).toString().padStart(2,'0');
        let s = (ultimoFichajeSegundos % 60).toString().padStart(2,'0');
        timerSpan.textContent = `${h}:${m}:${s}`;
    }
}

async function ejecutarFichaje(tipo) {
    let url = "../app/controllers/FichajeController.php?action=registrar";
    let ahora = new Date();
    let horaCliente = ahora.toISOString();

    try {
        let response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 
                'tipo': tipo,
                'hora_cliente': horaCliente
            })
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

            // Actualizar tipo
            ultimoTipo = tipo;

            if(tipo === 'entrada') {
                // Inicio de jornada: reiniciar y arrancar cronómetro
                ultimoFichajeSegundos = 0;
                iniciarCronometro();
                setTimeout(() => location.reload(), 100);
            } else if(tipo === 'inicio_descanso') {
                // Pausa: detener cronómetro y guardar tiempo
                if(cronometroInterval) {
                    clearInterval(cronometroInterval);
                    sessionStorage.setItem('ultimoFichajeSegundos', ultimoFichajeSegundos);
                    setTimeout(() => location.reload(), 100);
                }
            } else if(tipo === 'fin_descanso') {
                // Fin de descanso: continuar cronómetro desde tiempo guardado
                iniciarCronometro();
                setTimeout(() => location.reload(), 100);
            } else if(tipo === 'salida') {
                // Salida: detener cronómetro, limpiar localStorage y recargar página
                if(cronometroInterval) clearInterval(cronometroInterval);
                sessionStorage.removeItem('ultimoFichajeSegundos');
                setTimeout(() => location.reload(), 100); // Pequeño delay para que se vea Swal
            }

            // Actualizar botones
            actualizarBotones();

        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
}
logoutBtn();
function logoutBtn() {
    let logOutBtn = document.getElementById('btnLogout');
    if (!logOutBtn) return;


logOutBtn.addEventListener('click', async (e) => {
    e.preventDefault(); // evitar comportamiento por defecto

    //Preguntar al usuario antes de cerrar sesión
    let result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Se cerrará tu sesión actual",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    });

    //Si confirma, hacemos el logout
    if (result.isConfirmed) {
        try {
            let response = await fetch('/app/controllers/AuthController.php?action=logout', {
                method: 'POST',
                credentials: 'same-origin'
            });
            let data = await response.json();

            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Sesión cerrada',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
                // Redirigimos despues de un pequeño delay
                setTimeout(() => {
                    window.location.href = '/public/index.php';
                }, 500);
            } else {
                Swal.fire('Error', 'No se pudo cerrar sesión', 'error');
            }
        } catch (err) {
            console.error('Error en logout:', err);
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        }
    
    }
});
}

let recordsPerPage = 5; // registros por página
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
                <td><span class="tag-active">${reg.tipo.replace('_', ' ').toUpperCase()}</span></td>
            </tr>
        `;
    });

    // Actualizar botones de paginación
    document.getElementById("currentPage").textContent = page;
    document.getElementById("prevPage").disabled = page === 1;
    document.getElementById("nextPage").disabled = end >= historial.length;
}

// Botones de paginación
document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        renderTablePage(currentPage);
    }
});

document.getElementById("nextPage").addEventListener("click", () => {
    if (currentPage * recordsPerPage < historial.length) {
        currentPage++;
        renderTablePage(currentPage);
    }
});

// Inicializar
renderTablePage(currentPage);

// Eventos
btnEntrada.addEventListener('click', () => ejecutarFichaje('entrada'));
btnSalida.addEventListener('click', () => ejecutarFichaje('salida'));
btnPausa.addEventListener('click', () => {
    let accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
    ejecutarFichaje(accion);
});

// Inicializar botones y cronómetro según el último tipo
actualizarBotones();
iniciarCronometro();