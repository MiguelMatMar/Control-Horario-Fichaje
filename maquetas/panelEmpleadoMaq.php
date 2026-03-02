<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - TimeControl</title>
    
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/side.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/user-panel.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="app-container">
        
        <?php include 'headerMaq.php'; ?>

        <div class="app-content">
            
            <?php include 'sideMaq.php'; ?>

            <main class="main-content">
                
                <section class="dashboard-header">
                    <h1>¡Hola, Gorka!</h1>
                    <p>Hoy es lunes, 2 de marzo de 2026. Tu jornada aún no ha comenzado.</p>
                </section>

                <section class="punch-card-container">
                    <article class="admin-card punch-card">
                        <div class="timer-display">
                            <span id="timer">00:00:00</span>
                            <p class="timer-label">Tiempo transcurrido hoy</p>
                        </div>

                        <div class="punch-actions">
                            <button class="btn-punch btn-entry">
                                <i class="fas fa-play"></i> Fichar Entrada
                            </button>
                            
                            <div class="secondary-actions">
                                <button class="btn-punch btn-pause" disabled>
                                    <i class="fas fa-coffee"></i> Descanso
                                </button>
                                <button class="btn-punch btn-exit" disabled>
                                    <i class="fas fa-stop"></i> Fichar Salida
                                </button>
                            </div>
                        </div>
                    </article>

                    <article class="admin-card day-summary">
                        <h3>Detalles de hoy</h3>
                        <div class="stat-row"><span>Entrada:</span> <strong>--:--</strong></div>
                        <div class="stat-row"><span>Salida esperada:</span> <strong>17:30</strong></div>
                        <div class="stat-row"><span>Estado:</span> <span class="tag-pending">No iniciado</span></div>
                    </article>
                </section>

                <section class="admin-card table-section">
                    <div class="table-header">
                        <h3>Mis últimos registros</h3>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Total Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>27/02/2026</td>
                                <td>08:30</td>
                                <td>17:45</td>
                                <td>8h 15min</td>
                            </tr>
                            <tr>
                                <td>26/02/2026</td>
                                <td>08:25</td>
                                <td>17:30</td>
                                <td>8h 05min</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>

    <?php include 'footerMaq.php'; ?>

    <script>
        // Lógica de submenús del sidebar
        document.querySelectorAll('.submenu-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.parentElement.classList.toggle('open');
            });
        });

        // Aquí iría el JS del cronómetro cuando el usuario pulse "Entrada"
    </script>
</body>
</html>