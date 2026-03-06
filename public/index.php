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