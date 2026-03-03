<?php
// index.php

// Habilitar sesiones
session_start();

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Si no hay sesión iniciada → login
if (!isset($_SESSION['user_id'])) {
    include __DIR__ . '/../app/views/auth/login.php';
    exit;
}

enum roles: int{
    case Admin = 1;
    case Trabajador = 2;
    case Cliente = 3;
}
// Según rol, cargar dashboard correspondiente
switch ($_SESSION['role_id']) {
    case roles::Admin->value: // Admin
        include __DIR__ . '/../app/views/dashboard/admin.php';
        break;
    case roles::Trabajador->value: // Trabajador
    case roles::Cliente->value: // Cliente
        include __DIR__ . '/../app/views/dashboard/empleado.php';
        break;
    default:
        echo "Rol desconocido";
        break;
}
// Según rol, redirigir al dashboard correspondiente
switch ($_SESSION['role_id']) {
    case roles::Admin->value: // Admin
        header('Location: /../app/views/dashboard/admin.php');
        exit;
    case roles::Trabajador->value: // Trabajador
        header('Location: /../app/views/dashboard/empleado.php');
    default:
        echo "Rol desconocido";
        break;
}