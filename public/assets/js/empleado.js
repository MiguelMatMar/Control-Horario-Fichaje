let ultimoTipo = window.ultimoTipoGlobal || "ninguno"; // Lo inicializaremos desde PHP
let ultimoFichajeSegundos = 0;
let cronometroInterval = null;

const btnEntrada = document.getElementById('btn-entrada');
const btnPausa = document.getElementById('btn-pausa');
const btnSalida = document.getElementById('btn-salida');
const timerSpan = document.getElementById('timer');

function actualizarBotones() {
    btnEntrada.disabled = !(ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnPausa.disabled = (ultimoTipo === 'ninguno' || ultimoTipo === 'salida');
    btnSalida.disabled = !(ultimoTipo === 'entrada' || ultimoTipo === 'fin_descanso');
    btnPausa.querySelector('span').textContent = (ultimoTipo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso';
}

function iniciarCronometro() {
    if(cronometroInterval) clearInterval(cronometroInterval);

    if(['entrada','fin_descanso'].includes(ultimoTipo)){
        cronometroInterval = setInterval(() => {
            ultimoFichajeSegundos++;
            const h = Math.floor(ultimoFichajeSegundos / 3600).toString().padStart(2,'0');
            const m = Math.floor((ultimoFichajeSegundos % 3600) / 60).toString().padStart(2,'0');
            const s = (ultimoFichajeSegundos % 60).toString().padStart(2,'0');
            timerSpan.textContent = `${h}:${m}:${s}`;
        }, 1000);
    } else {
        timerSpan.textContent = '00:00:00';
    }
}

async function ejecutarFichaje(tipo) {
    const url = "../app/controllers/FichajeController.php?action=registrar";
    const ahora = new Date();
    const horaCliente = ahora.toISOString();

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 
                'tipo': tipo,
                'hora_cliente': horaCliente
            })
        });

        const data = await response.json();

        if(data.status === 'success'){
            Swal.fire({
                icon: 'success',
                title: '¡Hecho!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });
            ultimoTipo = tipo;
            if(tipo === 'entrada' || tipo === 'fin_descanso') {
                ultimoFichajeSegundos = 0;
                iniciarCronometro();
            } else {
                if(cronometroInterval) clearInterval(cronometroInterval);
            }
            actualizarBotones();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
}

// Eventos
btnEntrada.addEventListener('click', () => ejecutarFichaje('entrada'));
btnSalida.addEventListener('click', () => ejecutarFichaje('salida'));
btnPausa.addEventListener('click', () => {
    const accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
    ejecutarFichaje(accion);
});

// Inicializar botones
actualizarBotones();
timerSpan.textContent = '00:00:00';