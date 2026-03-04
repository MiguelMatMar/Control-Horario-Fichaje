<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!enum_exists('rol')) {
    enum rol: int {
        case admin = 1;
        case empleado = 2;
    }
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != rol::admin->value) {
    header("Location: /public/login.php");
    exit;
}


$esAdmin = ($_SESSION['role_id'] ?? 0) == rol::admin->value;
$textoRol = $esAdmin ? 'Administrador' : 'Empleado';
$nombreUsuario = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TimeControl - Administración</title>
    <link rel="stylesheet" href="/public/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="main-header">
    <div class="header-left">
        <h1 class="app-logo">Time<span>Control</span></h1>
    </div>

    <div class="header-right">
        <div class="user-info">
            <p class="user-name"><?php echo $nombreUsuario . " - " . "($textoRol)"; ?></p>
        </div>
        <div class="header-actions">
            <button id="btnLogout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Salir</span>
            </button>
        </div>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="/public/index.php" class="active">
                        <span>Dashboard Admin</span>
                    </a>
                </li>
                <li>
                    <a href="#" id="menuUsers">
                        <span>Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="#" id="menuRecords">
                        <span>Registros</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span>Informes</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <section class="page-header">
            <h1>Panel de Administración</h1>
            <p>Resumen general del sistema</p>
        </section>

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

        <section class="admin-filters">
            <form id="filterForm">
                <select name="user_id" id="filterUser"></select>
                <input type="date" name="fecha_inicio">
                <input type="date" name="fecha_fin">
                <button type="submit">Filtrar</button>
            </form>
            <button id="btnExport" style="margin-top: 10px;">Exportar CSV</button>
        </section>

        <section class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Fecha y hora</th>
                    </tr>
                </thead>
                <tbody id="recordsTable"></tbody>
            </table>
            <div id="paginationControls" style="margin-top: 15px;">
                <button id="prevPage" disabled class="prevPage">Anterior</button>
                <span id="currentPage">1</span>
                <button id="nextPage" class="nextPage">Siguiente</button>
            </div>
        </section>

        <section class="admin-charts">
            <canvas id="monthlyChart"></canvas>
        </section>
    </main>
</div>

<footer class="main-footer">
    <p>&copy; <?php echo date('Y'); ?> Control Horario - Proyecto DAW.</p>
</footer>

<script src="/public/assets/js/admin.js"></script>
</body>
</html>