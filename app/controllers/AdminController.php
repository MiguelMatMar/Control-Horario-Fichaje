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
        if (!AuthController::checkAuth() || !AuthController::checkRole(roles::Admin->value)) {
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'Acceso denegado']);
            exit;
        }

        $this->fichajeModel = new Fichaje();
        $this->userModel = new User();
    }

    // =========================
    // Listar fichajes
    // =========================
    public function listarFichajes(): void
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

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

        $total = count($fichajes);
        $offset = ($page - 1) * $limit;
        $paginated = array_slice($fichajes, $offset, $limit);
        $hasMore = ($offset + $limit) < $total;

        echo json_encode([
            'status' => 'success',
            'fichajes' => $paginated,
            'total' => $total,
            'page' => $page,
            'hasMore' => $hasMore
        ]);
        exit;
    }

    // =========================
    // Listar usuarios
    // =========================
    public function listarUsuarios(): void
    {
        header('Content-Type: application/json');
        $usuarios = $this->userModel->getAll();
        echo json_encode([
            'status' => 'success',
            'total' => count($usuarios)
        ]);
        exit;
    }

    // =========================
    // Resumen diario
    // =========================
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

    // =========================
    // Resumen semanal
    // =========================
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

    // =========================
    // Resumen mensual
    // =========================
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

    // =========================
    // Exportar CSV
    // =========================
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

// =========================
// Inicialización
// =========================
if (session_status() === PHP_SESSION_NONE) session_start();

enum roles: int {
    case Admin = 1;
    case Trabajador = 2;
    case Cliente = 3;
}

$action = $_GET['action'] ?? '';
$adminController = new AdminController();

switch ($action) {
    case 'listarFichajes': $adminController->listarFichajes(); break;
    case 'resumenDiario': $adminController->resumenDiario(); break;
    case 'resumenSemanal': $adminController->resumenSemanal(); break;
    case 'resumenMensual': $adminController->resumenMensual(); break;
    case 'exportarCSV': $adminController->exportarCSV(); break;
    case 'listarUsuarios': $adminController->listarUsuarios(); break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status'=>'error','message'=>'Acción no válida']);
        exit;
}