<?php
session_start();

require_once __DIR__ . '/../models/Fichaje.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

class DashboardController
{
    private Fichaje $fichajeModel;
    private User $userModel;
    private int $userId;

    public function __construct()
    {
        // Verificar autenticación
        if (!AuthController::checkAuth()) {
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'Acceso denegado']);
            exit;
        }

        $this->fichajeModel = new Fichaje();
        $this->userModel = new User();
        $this->userId = $_SESSION['user_id']; // Usuario logueado
    }

    /* =====================================================
       Resumen diario del usuario logueado
    ===================================================== */
    public function resumenDiario(): void
    {
        header('Content-Type: application/json');

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $resumen = $this->fichajeModel->calcularHorasPorFecha($this->userId, $fecha);
        $horasExtra = $this->fichajeModel->horasExtra($this->userId, $fecha);

        echo json_encode([
            'status'=>'success',
            'fecha'=>$fecha,
            'horas_trabajadas'=>$resumen['horas_trabajadas'],
            'horas_descanso'=>$resumen['horas_descanso'],
            'horas_extra'=>$horasExtra
        ]);
    }

    /* =====================================================
       Resumen semanal del usuario logueado
    ===================================================== */
    public function resumenSemanal(): void
    {
        header('Content-Type: application/json');

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $resumen = $this->fichajeModel->resumenSemanal($this->userId, $fecha);

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
    }

    /* =====================================================
       Resumen mensual del usuario logueado
    ===================================================== */
    public function resumenMensual(): void
    {
        header('Content-Type: application/json');

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $resumen = $this->fichajeModel->resumenMensual($this->userId, $fecha);

        echo json_encode(['status'=>'success','resumen'=>$resumen]);
    }

    /* =====================================================
       Últimos fichajes del usuario logueado (para el dashboard)
    ===================================================== */
    public function ultimosFichajes(int $limite = 10): void
    {
        header('Content-Type: application/json');

        $fichajes = $this->fichajeModel->getFichajes($this->userId);
        $fichajes = array_slice(array_reverse($fichajes), 0, $limite);

        echo json_encode(['status'=>'success','fichajes'=>$fichajes]);
    }
}

/* =====================================================
   Punto de entrada para rutas de dashboard
===================================================== */
$action = $_GET['action'] ?? '';
$controller = new DashboardController();

switch ($action) {
    case 'resumenDiario':
        $controller->resumenDiario();
        break;
    case 'resumenSemanal':
        $controller->resumenSemanal();
        break;
    case 'resumenMensual':
        $controller->resumenMensual();
        break;
    case 'ultimosFichajes':
        $controller->ultimosFichajes();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status'=>'error','message'=>'Acción no válida']);
        break;
}