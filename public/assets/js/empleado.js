/**
 * empleado.js
 * Gestión de cronómetro, fichajes, UI y persistencia de estado.
 */

// Variables de estado global
let ultimoTipo = "ninguno"; 
let ultimoFichajeSegundos = 0;
let cronometroInterval = null;

// Referencias al DOM
const btnEntrada = document.getElementById('btn-entrada');
const btnPausa = document.getElementById('btn-pausa');
const btnSalida = document.getElementById('btn-salida');
const timerSpan = document.getElementById('timer');

// Paginación de Historial
let recordsPerPage = 5;
let currentPage = 1;
let historial = window.historialEmpleado || [];

// ---------- FUNCIONES DE UI Y FORMATO ----------

/**
 * Actualiza la habilitación de botones y textos según el estado del trabajador
 */
function actualizarBotones() {
    if (!btnEntrada || !btnPausa || !btnSalida) return;

    btnEntrada.disabled = !(ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnPausa.disabled = (ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnSalida.disabled = !(ultimoTipo === 'entrada' || ultimoTipo === 'fin_descanso');

    const spanPausa = btnPausa.querySelector('span');
    if (spanPausa) {
        spanPausa.textContent = (ultimoTipo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso';
    }
}

/**
 * Formatea segundos a HH:MM:SS
 */
function formatTiempo(segundos) {
    let h = Math.floor(segundos / 3600).toString().padStart(2, '0');
    let m = Math.floor((segundos % 3600) / 60).toString().padStart(2, '0');
    let s = (segundos % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

/**
 * Actualiza todas las tarjetas de resumen (Hoy, Semana, Mes, Histórico)
 * Esto quita los "..." y pone el formato 0.00h
 */
async function cargarResumenesCards() {
    try {
        // Añadimos timestamp para evitar caché
        const resp = await fetch('../app/controllers/FichajeController.php?action=resumen_completo&t=' + Date.now());
        const res = await resp.json();
        
        if (res.status === 'success') {
            const mappings = {
                'horas-hoy': res.data.hoy.total_horas_trabajadas,
                'horas-semanales': res.data.semanal.total_horas_trabajadas,
                'horas-mensuales': res.data.mensual.total_horas_trabajadas,
                'horas-historicas': res.data.historico.total_horas_trabajadas
            };

            for (const [id, valor] of Object.entries(mappings)) {
                const el = document.getElementById(id);
                if (el) {
                    // Si el servidor no devuelve nada, ponemos 0.00
                    const num = valor || "0.00";
                    el.textContent = num + "h";
                }
            }
        }
    } catch (e) {
        console.warn("Error al actualizar tarjetas de resumen:", e);
    }
}

// ---------- LÓGICA DEL CRONÓMETRO ----------

// Añade esta variable al inicio del archivo
let tiempoReferenciaServidor = Date.now(); 

// Variable para guardar el momento del fichaje

let fechaReferencia = window.estadoUsuario.fechaUltimo ? new Date(window.estadoUsuario.fechaUltimo) : null;

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

// Nueva función para no crear conflictos con la existente pero mejorar la sincronía
async function actualizarEstadoUsuarioSync(segundos) {
    try {
        await fetch('../app/controllers/EstadoController.php?action=updateEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'tipo': ultimoTipo,
                'segundos': segundos // Enviamos los segundos calculados reales
            })
        });
    } catch (err) {
        console.error("Error sincronizando estado:", err);
    }
}

// ---------- ACCIONES DE FICHAJE ----------

async function ejecutarFichaje(tipo) {
    try {
        const response = await fetch("../app/controllers/FichajeController.php?action=registrar", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'tipo': tipo })
        });
        const data = await response.json();

        if (data.status === 'success') {
            ultimoTipo = tipo;
            
            // 1. Manejo del cronómetro visual (esto es solo para el reloj de la pantalla)
            if (tipo === 'salida') {
                ultimoFichajeSegundos = 0;
                if (cronometroInterval) clearInterval(cronometroInterval);
                if (timerSpan) timerSpan.textContent = "00:00:00";
            }

            // 2. Guardar estado en DB (Importante: que termine antes de seguir)
            await actualizarEstadoUsuario();

            // 3. ACTUALIZAR CONTADORES (Semanal, Mensual, Histórico)
            // Llamamos a la función que pide los nuevos cálculos al servidor
            await cargarResumenesCards();

            // 4. Feedback al usuario
            Swal.fire({
                icon: 'success',
                title: '¡Hecho!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });

            // 5. Recarga controlada
            // Usamos un tiempo ligeramente superior para que la DB procese el registro
            setTimeout(() => {
                location.reload();
            }, 300);

        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (e) {
        console.error("Error en fichaje:", e);
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
}

async function actualizarEstadoUsuario() {
    try {
        await fetch('../app/controllers/EstadoController.php?action=updateEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'tipo': ultimoTipo,
                'segundos': ultimoFichajeSegundos
            })
        });
    } catch (err) {
        console.error("Error sincronizando estado:", err);
    }
}

// ---------- PAGINACIÓN ----------

function renderTablePage(page = 1) {
    const tbody = document.querySelector(".admin-table tbody");
    if (!tbody) return;

    const start = (page - 1) * recordsPerPage;
    const end = start + recordsPerPage;
    const pageRecords = historial.slice(start, end);

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

    document.getElementById("currentPage").textContent = page;
    document.getElementById("prevPage").disabled = page === 1;
    document.getElementById("nextPage").disabled = end >= historial.length;
}

// ---------- INICIALIZACIÓN ----------

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Cargar estado inicial desde DB
    try {
        const resp = await fetch('../app/controllers/EstadoController.php?action=getEstado');
        const data = await resp.json();
        
        if (data.status === 'success') {
            ultimoTipo = data.tipo || "ninguno";
            ultimoFichajeSegundos = parseInt(data.segundos) || 0;
            
            actualizarBotones();
            iniciarCronometro();
            
            // Quitar los "..." y poner las horas reales al cargar
            await cargarResumenesCards(); 
        }
    } catch (err) {
        console.error("Error en carga inicial:", err);
    }

    // 2. Renderizar historial
    renderTablePage(currentPage);

    // 3. Listeners de botones de fichaje
    if (btnEntrada) btnEntrada.onclick = () => ejecutarFichaje('entrada');
    if (btnSalida) btnSalida.onclick = () => ejecutarFichaje('salida');
    if (btnPausa) {
        btnPausa.onclick = () => {
            const accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
            ejecutarFichaje(accion);
        };
    }

    // 4. Listeners de paginación
    const btnPrev = document.getElementById("prevPage");
    const btnNext = document.getElementById("nextPage");
    if (btnPrev) btnPrev.onclick = () => { if (currentPage > 1) { currentPage--; renderTablePage(currentPage); } };
    if (btnNext) btnNext.onclick = () => { if (currentPage * recordsPerPage < historial.length) { currentPage++; renderTablePage(currentPage); } };

    // 5. Logout
    logoutBtn();
});
// Hilo independiente para actualizar el recuadro de estadísticas "Hoy"
setInterval(() => {
    // Solo actuamos si el usuario está en jornada (entrada o fin_descanso)
    if (['entrada', 'fin_descanso','inicio_descanso'].includes(ultimoTipo)) {
        const cuadroHoy = document.getElementById('horas-hoy');
        if (cuadroHoy) {
            // Usamos la variable global 'ultimoFichajeSegundos' que ya se actualiza sola
            // Convertimos esos segundos a formato decimal (ej: 1.25h)
            const horasDecimales = (ultimoFichajeSegundos / 3600).toFixed(2);
            cuadroHoy.textContent = horasDecimales + "h";
        }
    }
}, 1000); // Se ejecuta cada segundo
function logoutBtn() {
    const btnLogout = document.getElementById('btnLogout');
    if (!btnLogout) return;

    btnLogout.onclick = async (e) => {
        e.preventDefault();
        
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Se cerrará tu sesión actual",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/app/controllers/AuthController.php?action=logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.status === 'success') {
                    window.location.href = '/public/index.php';
                } else {
                    Swal.fire('Error', 'No se pudo cerrar sesión', 'error');
                }
            } catch (err) {
                console.error("Error al cerrar sesión:", err);
                // Si falla el fetch, redirigimos igual por seguridad
                window.location.href = '/public/index.php';
            }
        }
    };
}