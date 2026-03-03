<?php

require_once __DIR__ . '/../models/Fichaje.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';


class AdminController
{
    private Fichaje $fichajeModel;
    private User $userModel;

    public function __construct()
    {
        // Solo admins
        if (!AuthController::checkAuth() || !AuthController::checkRole(roles::Admin->value)) {
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'Acceso denegado']);
            exit;
        }

        $this->fichajeModel = new Fichaje();
        $this->userModel = new User();
    }

    /* =====================================================
       Listar fichajes globales
       GET: user_id (opcional), fechaInicio, fechaFin
    ===================================================== */
    public function listarFichajes(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        $fichajes = [];
        if ($userId) {
            $fichajes = $this->fichajeModel->getFichajes($userId, $fechaInicio, $fechaFin);
        } else {
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $f = $this->fichajeModel->getFichajes($u['id'], $fechaInicio, $fechaFin);
                $fichajes = array_merge($fichajes, $f);
            }
        }

        echo json_encode(['status'=>'success','fichajes'=>$fichajes]);
        exit;
    }

    /* =====================================================
       Resumen diario global o por usuario
       GET: user_id (opcional), fecha
    ===================================================== */
    public function resumenDiario(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $resumen = [];

        if ($userId) {
            $resumen = $this->fichajeModel->calcularHorasPorFecha($userId, $fecha);
        } else {
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $resumen[$u['nombre']] = $this->fichajeModel->calcularHorasPorFecha($u['id'], $fecha);
            }
        }

        echo json_encode(['status'=>'success','fecha'=>$fecha,'resumen'=>$resumen]);
        exit;
    }

    /* =====================================================
       Resumen semanal global o por usuario
       GET: user_id (opcional), fecha
    ===================================================== */
    public function resumenSemanal(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $resumen = [];

        if ($userId) {
            $resumen = $this->fichajeModel->resumenSemanal($userId, $fecha);
        } else {
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $resumen[$u['nombre']] = $this->fichajeModel->resumenSemanal($u['id'], $fecha);
            }
        }

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
        exit;
    }

    /* =====================================================
       Resumen mensual global o por usuario
       GET: user_id (opcional), fecha
    ===================================================== */
    public function resumenMensual(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $resumen = [];

        if ($userId) {
            $resumen = $this->fichajeModel->resumenMensual($userId, $fecha);
        } else {
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $resumen[$u['nombre']] = $this->fichajeModel->resumenMensual($u['id'], $fecha);
            }
        }

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
        exit;
    }

    /* =====================================================
       Exportar fichajes a CSV global o por usuario
       GET: user_id (opcional), fechaInicio, fechaFin
    ===================================================== */
    public function exportarCSV(): void
    {
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        $fichajes = [];
        if ($userId) {
            $fichajes = $this->fichajeModel->getFichajes($userId, $fechaInicio, $fechaFin);
        } else {
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $f = $this->fichajeModel->getFichajes($u['id'], $fechaInicio, $fechaFin);
                $fichajes = array_merge($fichajes, $f);
            }
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fichajes_admin.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID','Usuario','Tipo','FechaHora']);

        foreach ($fichajes as $f) {
            $usuario = $this->userModel->findById($f['user_id']);
            fputcsv($output, [
                $f['id'],
                $usuario['nombre'] ?? 'Desconocido',
                $f['tipo'],
                $f['fecha_hora']
            ]);
        }

        fclose($output);
        exit;
    }
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegurarse de definir los roles si aún no lo hiciste
enum roles: int {
    case Admin = 1;
    case Trabajador = 2;
    case Cliente = 3;
}

// Instanciar el controlador
$action = $_GET['action'] ?? '';
$adminController = new AdminController();

// Ejecutar acción según switch
switch ($action) {
    case 'listarFichajes':
        $adminController->listarFichajes();
        break;
    case 'resumenDiario':
        $adminController->resumenDiario();
        break;
    case 'resumenSemanal':
        $adminController->resumenSemanal();
        break;
    case 'resumenMensual':
        $adminController->resumenMensual();
        break;
    case 'exportarCSV':
        $adminController->exportarCSV();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no válida'
        ]);
        exit;
}