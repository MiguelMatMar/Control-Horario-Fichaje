<?php
session_start();

require_once __DIR__ . '/../models/Fichaje.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

enum roles: int{
    case Admin = 1;
    case Trabajador = 2;
    case Cliente = 3;
}

class ReporteController
{
    private Fichaje $fichajeModel;
    private User $userModel;

    public function __construct()
    {
        $this->fichajeModel = new Fichaje();
        $this->userModel = new User();

        // Solo admins pueden acceder
        if (!AuthController::checkAuth() || !AuthController::checkRole(roles::Admin->value)) {
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'Acceso denegado']);
            exit;
        }
    }

    /* =====================================================
       Listar fichajes
       GET: user_id (opcional), fechaInicio, fechaFin
    ===================================================== */
    public function listar(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        if ($userId) {
            $fichajes = $this->fichajeModel->getFichajes($userId, $fechaInicio, $fechaFin);
        } else {
            // Para todos los usuarios (admin)
            $fichajes = [];
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $f = $this->fichajeModel->getFichajes($u['id'], $fechaInicio, $fechaFin);
                $fichajes = array_merge($fichajes, $f);
            }
        }

        echo json_encode(['status'=>'success','fichajes'=>$fichajes]);
    }

    /* =====================================================
       Resumen diario
       GET: user_id, fecha
    ===================================================== */
    public function resumenDiario(): void
    {
        header('Content-Type: application/json');

        $userId = intval($_GET['user_id'] ?? 0);
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if (!$userId) {
            echo json_encode(['status'=>'error','message'=>'Usuario no válido']);
            return;
        }

        $resumen = $this->fichajeModel->calcularHorasPorFecha($userId, $fecha);
        $horasExtra = $this->fichajeModel->horasExtra($userId, $fecha);

        echo json_encode([
            'status'=>'success',
            'fecha'=>$fecha,
            'horas_trabajadas'=>$resumen['horas_trabajadas'],
            'horas_descanso'=>$resumen['horas_descanso'],
            'horas_extra'=>$horasExtra
        ]);
    }

    /* =====================================================
       Resumen semanal
       GET: user_id, fecha (cualquier día de la semana)
    ===================================================== */
    public function resumenSemanal(): void
    {
        header('Content-Type: application/json');

        $userId = intval($_GET['user_id'] ?? 0);
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if (!$userId) {
            echo json_encode(['status'=>'error','message'=>'Usuario no válido']);
            return;
        }

        $resumen = $this->fichajeModel->resumenSemanal($userId, $fecha);

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
    }

    /* =====================================================
       Resumen mensual
       GET: user_id, fecha (cualquier día del mes)
    ===================================================== */
    public function resumenMensual(): void
    {
        header('Content-Type: application/json');

        $userId = intval($_GET['user_id'] ?? 0);
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if (!$userId) {
            echo json_encode(['status'=>'error','message'=>'Usuario no válido']);
            return;
        }

        $resumen = $this->fichajeModel->resumenMensual($userId, $fecha);

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
    }

    /* =====================================================
       Exportar fichajes a CSV
       GET: user_id (opcional), fechaInicio, fechaFin
    ===================================================== */
    public function exportarCSV(): void
    {
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        if ($userId) {
            $fichajes = $this->fichajeModel->getFichajes($userId, $fechaInicio, $fechaFin);
        } else {
            $fichajes = [];
            $usuarios = $this->userModel->getAll();
            foreach ($usuarios as $u) {
                $f = $this->fichajeModel->getFichajes($u['id'], $fechaInicio, $fechaFin);
                $fichajes = array_merge($fichajes, $f);
            }
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fichajes.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Usuario', 'Tipo', 'FechaHora']);

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

/* =====================================================
   Punto de entrada para rutas de reporte
===================================================== */
$action = $_GET['action'] ?? '';
$controller = new ReporteController();

switch ($action) {
    case 'listar':
        $controller->listar();
        break;
    case 'resumenDiario':
        $controller->resumenDiario();
        break;
    case 'resumenSemanal':
        $controller->resumenSemanal();
        break;
    case 'resumenMensual':
        $controller->resumenMensual();
        break;
    case 'exportarCSV':
        $controller->exportarCSV();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status'=>'error','message'=>'Acción no válida']);
        break;
}