<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// 1. CARGA DE MODELOS (Rutas desde la ubicación física del archivo)
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Fichaje.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Si estás en public/index.php, la redirección es local
    exit;
}

$fichajeModel = new Fichaje();
$userId = $_SESSION['user_id'];

// 2. OBTENER DATOS DEL ESTADO ACTUAL
$ultimoFichaje = $fichajeModel->ultimoFichaje($userId);
$tipoUltimo = $ultimoFichaje ? $ultimoFichaje['tipo'] : 'ninguno';
$resumenHoy = $fichajeModel->calcularHorasPorFecha($userId, date('Y-m-d'));
$historial = $fichajeModel->getFichajes($userId, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));

// 3. LÓGICA DE ETIQUETAS Y ESTADOS
$estadoTexto = "No iniciado";
$estadoClase = "tag-pending";

if (in_array($tipoUltimo, ['entrada', 'fin_descanso'])) {
    $estadoTexto = "En jornada";
    $estadoClase = "tag-active";
} elseif ($tipoUltimo === 'inicio_descanso') {
    $estadoTexto = "En descanso";
    $estadoClase = "tag-pause";
} elseif ($tipoUltimo === 'salida') {
    $estadoTexto = "Jornada finalizada";
    $estadoClase = "tag-completed";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - TimeControl</title>
    
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/side.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/user-panel.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="app-container">
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <div class="app-content">
            <?php include __DIR__ . '/../partials/sidebar.php'; ?>

            <main class="main-content">
                <section class="dashboard-header">
                    <h1>¡Hola, <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>!</h1>
                    <p>Hoy es <?php echo date('d/m/Y'); ?>. Tu estado actual es: <strong class="<?php echo $estadoClase; ?>"><?php echo $estadoTexto; ?></strong></p>
                </section>

                <section class="punch-card-container">
                    <article class="admin-card punch-card">
                        <div class="timer-display">
                            <span id="timer">00:00:00</span>
                            <p class="timer-label">Tiempo en la sesión actual</p>
                        </div>

                        <div class="punch-actions">
                            <button class="btn-punch btn-entry" id="btn-entrada" 
                                <?php echo ($tipoUltimo !== 'ninguno' && $tipoUltimo !== 'salida') ? 'disabled' : ''; ?>>
                                <i class="fas fa-play"></i> Fichar Entrada
                            </button>
                            
                            <div class="secondary-actions">
                                <button class="btn-punch btn-pause" id="btn-pausa"
                                    <?php echo (in_array($tipoUltimo, ['ninguno', 'salida'])) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-coffee"></i> 
                                    <span><?php echo ($tipoUltimo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso'; ?></span>
                                </button>

                                <button class="btn-punch btn-exit" id="btn-salida"
                                    <?php echo (!in_array($tipoUltimo, ['entrada', 'fin_descanso'])) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-stop"></i> Fichar Salida
                                </button>
                            </div>
                        </div>
                    </article>

                    <article class="admin-card day-summary">
                        <h3>Resumen de hoy</h3>
                        <div class="stat-row"><span>Trabajado:</span> <strong><?php echo $resumenHoy['horas_trabajadas'] ?? '0.00'; ?>h</strong></div>
                        <div class="stat-row"><span>Descanso:</span> <strong><?php echo $resumenHoy['horas_descanso'] ?? '0.00'; ?>h</strong></div>
                    </article>
                </section>

                <section class="admin-card table-section">
                    <h3>Mis últimos registros (7 días)</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historial)): ?>
                                <tr><td colspan="2">No hay registros recientes.</td></tr>
                            <?php else: 
                                foreach (array_reverse($historial) as $reg): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($reg['fecha_hora'])); ?></td>
                                    <td><span class="tag-active"><?php echo strtoupper(str_replace('_', ' ', $reg['tipo'])); ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        const ultimoTipo = "<?php echo $tipoUltimo; ?>";

        async function ejecutarFichaje(tipo) {
            // RUTA CORRECTA desde public/index.php
            const url = "../app/controllers/FichajeController.php?action=registrar";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 'tipo': tipo })
                });

                const text = await response.text();
                
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Hecho!',
                            text: data.message,
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (e) {
                    console.error("Respuesta no válida del servidor:", text);
                    Swal.fire('Error crítico', 'El servidor devolvió un error. Revisa la consola (F12).', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo conectar con el controlador.', 'error');
            }
        }

        // Eventos de botones
        document.getElementById('btn-entrada')?.addEventListener('click', () => ejecutarFichaje('entrada'));
        document.getElementById('btn-salida')?.addEventListener('click', () => ejecutarFichaje('salida'));
        document.getElementById('btn-pausa')?.addEventListener('click', () => {
            const accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
            ejecutarFichaje(accion);
        });

        // Lógica del Cronómetro
        <?php if (in_array($tipoUltimo, ['entrada', 'fin_descanso'])): ?>
            let segundosActuales = <?php echo max(0, time() - strtotime($ultimoFichaje['fecha_hora'])); ?>;
            const timerSpan = document.getElementById('timer');

            setInterval(() => {
                segundosActuales++;
                const h = Math.floor(segundosActuales / 3600).toString().padStart(2, '0');
                const m = Math.floor((segundosActuales % 3600) / 60).toString().padStart(2, '0');
                const s = (segundosActuales % 60).toString().padStart(2, '0');
                if(timerSpan) timerSpan.textContent = `${h}:${m}:${s}`;
            }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>