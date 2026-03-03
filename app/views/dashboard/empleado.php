<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// 1. Verificación de seguridad y carga de modelos
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Fichaje.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../public/index.php');
    exit;
}

$fichajeModel = new Fichaje();
$userId = $_SESSION['user_id'];

// 2. Obtener datos
$ultimoFichaje = $fichajeModel->ultimoFichaje($userId);
$tipoUltimo = $ultimoFichaje ? $ultimoFichaje['tipo'] : 'ninguno';
$resumenHoy = $fichajeModel->calcularHorasPorFecha($userId, date('Y-m-d'));
$historial = $fichajeModel->getFichajes($userId, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));

// Lógica de etiquetas
$estadoTexto = "No iniciado";
$estadoClase = "tag-pending";

if ($tipoUltimo === 'entrada' || $tipoUltimo === 'fin_descanso') {
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
    
    <link rel="stylesheet" href="../../../public/assets/css/layout.css">
    <link rel="stylesheet" href="../../../public/assets/css/header.css">
    <link rel="stylesheet" href="../../../public/assets/css/side.css">
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
    <link rel="stylesheet" href="../../../public/assets/css/user-panel.css"> 
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
                    <p>Hoy es <?php echo date('d/m/Y'); ?>. Tu estado actual es: <strong><?php echo $estadoTexto; ?></strong></p>
                </section>

                <section class="punch-card-container">
                    <article class="admin-card punch-card">
                        <div class="timer-display">
                            <span id="timer">00:00:00</span>
                            <p class="timer-label">Tiempo trabajado hoy (estimado)</p>
                        </div>

                        <div class="punch-actions">
                            <button class="btn-punch btn-entry" id="btn-entrada" 
                                <?php echo ($tipoUltimo !== 'ninguno' && $tipoUltimo !== 'salida') ? 'disabled' : ''; ?>>
                                <i class="fas fa-play"></i> Fichar Entrada
                            </button>
                            
                            <div class="secondary-actions">
                                <button class="btn-punch btn-pause" id="btn-pausa"
                                    <?php echo ($tipoUltimo === 'ninguno' || $tipoUltimo === 'salida') ? 'disabled' : ''; ?>>
                                    <i class="fas fa-coffee"></i> 
                                    <?php echo ($tipoUltimo === 'inicio_descanso') ? 'Fin Descanso' : 'Descanso'; ?>
                                </button>

                                <button class="btn-punch btn-exit" id="btn-salida"
                                    <?php echo ($tipoUltimo === 'ninguno' || $tipoUltimo === 'salida' || $tipoUltimo === 'inicio_descanso') ? 'disabled' : ''; ?>>
                                    <i class="fas fa-stop"></i> Fichar Salida
                                </button>
                            </div>
                        </div>
                    </article>

                    <article class="admin-card day-summary">
                        <h3>Detalles de hoy</h3>
                        <div class="stat-row"><span>Horas trabajadas:</span> <strong><?php echo $resumenHoy['horas_trabajadas'] ?? 0; ?>h</strong></div>
                        <div class="stat-row"><span>En descanso:</span> <strong><?php echo $resumenHoy['horas_descanso'] ?? 0; ?>h</strong></div>
                        <div class="stat-row"><span>Estado:</span> <span class="<?php echo $estadoClase; ?>"><?php echo $estadoTexto; ?></span></div>
                    </article>
                </section>

                <section class="admin-card table-section">
                    <div class="table-header">
                        <h3>Mis últimos registros</h3>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Acción</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historial)): ?>
                                <tr><td colspan="3">No hay registros recientes.</td></tr>
                            <?php else: 
                                foreach (array_reverse($historial) as $reg): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reg['fecha_hora'])); ?></td>
                                    <td><span class="tag-active"><?php echo strtoupper($reg['tipo']); ?></span></td>
                                    <td>Registro automático</td>
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
        // ... (Tu JS actual está bien, solo asegúrate de que el fetch apunte bien)
        const ultimoTipo = "<?php echo $tipoUltimo; ?>";

        async function ejecutarFichaje(tipo) {
            const formData = new URLSearchParams();
            formData.append('tipo', tipo);

            try {
                // Ruta al controlador desde app/views/dashboard/
                const response = await fetch('../../controllers/FichajeController.php?action=registrar', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Hecho!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }
        }

        document.getElementById('btn-entrada')?.addEventListener('click', () => ejecutarFichaje('entrada'));
        document.getElementById('btn-salida')?.addEventListener('click', () => ejecutarFichaje('salida'));
        document.getElementById('btn-pausa')?.addEventListener('click', () => {
            const accion = (ultimoTipo === 'inicio_descanso') ? 'fin_descanso' : 'inicio_descanso';
            ejecutarFichaje(accion);
        });

        <?php if ($tipoUltimo === 'entrada' || $tipoUltimo === 'fin_descanso'): ?>
            let totalSegundos = <?php echo (($resumenHoy['horas_trabajadas'] ?? 0) * 3600); ?>;
            setInterval(() => {
                totalSegundos++;
                const hrs = Math.floor(totalSegundos / 3600).toString().padStart(2, '0');
                const mins = Math.floor((totalSegundos % 3600) / 60).toString().padStart(2, '0');
                const secs = (totalSegundos % 60).toString().padStart(2, '0');
                document.getElementById('timer').textContent = `${hrs}:${mins}:${secs}`;
            }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>