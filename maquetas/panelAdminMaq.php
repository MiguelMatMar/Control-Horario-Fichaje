<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TimeControl</title>
    
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/side.css">
    <link rel="stylesheet" href="css/footer.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="app-container">
        
        <?php include 'headerMaq.php'; ?>

        <div class="app-content">
            
            <?php include 'sideMaq.php'; ?>

            <main class="main-content">
                
                <section class="dashboard-header">
                    <h1>Panel de Administración</h1>
                    <p>Gestión global de registros y empleados</p>
                </section>

                <section class="admin-grid">
                    <article class="admin-card chart-container">
                        <h3>Horas Trabajadas por Día</h3>
                        <div class="placeholder-chart">
                            <i class="fas fa-chart-bar fa-3x"></i>
                        </div>
                    </article>
                    
                    <article class="admin-card stats-container">
                        <h3>Resumen Semanal</h3>
                        <div class="stat-row"><span>Total:</span> 160h</div>
                        <div class="stat-row"><span>Extra:</span> 12h</div>
                    </article>
                </section>

                <section class="admin-card table-section">
                    <div class="table-header">
                        <h3>Registro Histórico de Fichajes</h3>
                        <button class="btn-primary"><i class="fas fa-download"></i> Exportar CSV</button>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gorka Martínez</td>
                                <td>02/03/2026</td>
                                <td>08:30</td>
                                <td>17:30</td>
                                <td><span class="tag-success">OK</span></td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>
    <?php include 'footerMaq.php'; ?>

    <script>
        document.querySelectorAll('.submenu-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.parentElement.classList.toggle('open');
            });
        });
    </script>
</body>
</html>