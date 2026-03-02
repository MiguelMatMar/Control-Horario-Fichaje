<?php
// routes/web.php

// Cargar controladores
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/FichajeController.php';
require_once __DIR__ . '/../app/controllers/ReporteController.php';

// Capturar acción
$action = $_GET['action'] ?? '';
$controllerName = $_GET['controller'] ?? '';

// Routing básico
switch ($controllerName) {
    case 'auth':
        $controller = new AuthController();
        break;
    case 'user':
        $controller = new UserController();
        break;
    case 'fichaje':
        $controller = new FichajeController();
        break;
    case 'reporte':
        $controller = new ReporteController();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Controlador no válido']);
        exit;
}

// Ejecutar acción si existe
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}