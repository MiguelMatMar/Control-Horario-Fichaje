<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
enum rol : int{
    case admin = 1;
    case empleado = 2;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Horario</title>

    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header class="main-header">
    <div class="logo">
        <h2>Control Horario</h2>
    </div>

    <div class="user-info">
        <span>
            <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            (<?php echo $_SESSION['role_id'] == rol::admin->value ? 'Administrador' : 'Empleado'; ?>)
        </span>

        <button id="btnLogout" class="btn-logout">Cerrar sesión</button>
    </div>
</header>

<div class="layout">