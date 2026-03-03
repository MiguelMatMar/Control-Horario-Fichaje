<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    echo "Acceso denegado";
    exit;
}

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<link rel="stylesheet" href="/../public/assets/css/admin.css">
<main class="admin-main">

    <section class="page-header">
        <h1>Panel de Administración</h1>
        <p>Resumen general del sistema</p>
    </section>

    <!-- Tarjetas resumen -->
    <section class="admin-stats">
        <div class="card">
            <h3>Total empleados</h3>
            <p id="totalUsers">-</p>
        </div>

        <div class="card">
            <h3>Fichajes hoy</h3>
            <p id="todayRecords">-</p>
        </div>

        <div class="card">
            <h3>Horas extra esta semana</h3>
            <p id="extraHours">-</p>
        </div>
    </section>

    <!-- Filtros -->
    <section class="admin-filters">
        <h2>Filtrar registros</h2>

        <form id="filterForm">
            <select name="user_id" id="filterUser">
                
            </select>

            <input type="date" name="fecha_inicio">
            <input type="date" name="fecha_fin">

            <button type="submit">Filtrar</button>
        </form>

        <button id="btnExport">Exportar CSV</button>
    </section>

    <!-- Tabla -->
    <section class="admin-table">
        <h2>Histórico de fichajes</h2>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Tipo</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody id="recordsTable">
                <!-- Se carga por AJAX -->
            </tbody>
        </table>
        <div id="paginationControls" class="pagination">
            <button id="prevPage" disabled>Anterior</button>
            <span id="currentPage">1</span>
            <button id="nextPage">Siguiente</button>
        </div>
    </section>

    <!-- Gráfico -->
    <section class="admin-charts">
        <h2>Resumen mensual</h2>
        <canvas id="monthlyChart"></canvas>
    </section>

</main>

<script src="/../public/assets/js/admin.js"></script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>