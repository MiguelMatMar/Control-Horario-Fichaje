<?php
if (session_status() == PHP_SESSION_NONE) session_start();

if (!enum_exists('rol')) {
    enum rol: int { case admin = 1; case empleado = 2; }
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != rol::admin->value) {
    header("Location: /public/login.php");
    exit;
}

$nombreUsuario = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario');
$textoRol = ($_SESSION['role_id'] ?? 0) == rol::admin->value ? 'Administrador' : 'Empleado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TimeControl - Administración</title>
    <link rel="stylesheet" href="/public/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="main-header">
    <div class="header-left">
        <h2>Time<span>Control</span></h2>
    </div>
    <div class="header-right">
        <div class="user-info">
            <p><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></p>
        </div>
        <button id="btnLogout" class="btn-logout">Salir</button>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <nav>
            <ul>
                <li><a href="/public/index.php">Dashboard Admin</a></li>
                <li><a href="#" class="active">Usuarios</a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <section class="page-header">
            <h1>Gestión de Usuarios</h1>
            <p>Administra los usuarios del sistema</p>
        </section>

        <section class="admin-filters">
            <form id="filterForm">
                <input type="text" id="filterNombre" placeholder="Nombre">
                <input type="text" id="filterEmail" placeholder="Email">
                <select id="filterRol">
                    <option value="">Todos los roles</option>
                    <option value="1">Admin</option>
                    <option value="2">Empleado</option>
                </select>
                <button type="button" id="btnFiltrar">Filtrar</button>
            </form>
        </section>

        <section class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody"></tbody>
            </table>

            <div id="pagination" style="margin-top: 15px; display: flex; gap: 5px;">
                </div>
        </section>
    </main>
</div>

<footer class="main-footer">
    <p>&copy; <?php echo date('Y'); ?> Control Horario - Proyecto DAW.</p>
</footer>

<script src="/public/assets/js/usuariosList.js"></script>
</body>
</html>