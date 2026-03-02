<?php

require_once __DIR__ . '/../models/Fichaje.php';
require_once __DIR__ . '/AuthController.php';

class FichajeController
{
    private Fichaje $fichajeModel;

    public function __construct()
    {
        $this->fichajeModel = new Fichaje();
    }

    /* =====================================================
       Registrar fichaje
       POST: tipo (entrada, salida, inicio_descanso, fin_descanso)
       Devuelve JSON para SweetAlert
    ===================================================== */
    public function registrar(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status'=>'error','message'=>'Debes iniciar sesión']);
            exit;
        }

        $tipo = $_POST['tipo'] ?? '';
        $userId = $_SESSION['user_id'];

        try {
            $this->fichajeModel->registrar($userId, $tipo);

            echo json_encode([
                'status'=>'success',
                'message'=>"Fichaje '$tipo' registrado correctamente"
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status'=>'error',
                'message'=>$e->getMessage()
            ]);
        }
    }

    /* =====================================================
       Obtener últimos fichajes del usuario
       GET: devuelve JSON con fichajes recientes
    ===================================================== */
    public function ultimosFichajes(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status'=>'error','message'=>'Debes iniciar sesión']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $fichajes = $this->fichajeModel->getFichajes($userId, date('Y-m-d'));

        echo json_encode([
            'status'=>'success',
            'fichajes'=>$fichajes
        ]);
    }

    /* =====================================================
       Resumen diario del usuario
       GET: devuelve JSON con horas trabajadas y descansos
    ===================================================== */
    public function resumenDiario(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status'=>'error','message'=>'Debes iniciar sesión']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $resumen = $this->fichajeModel->calcularHorasPorFecha($userId, $fecha);

        echo json_encode([
            'status'=>'success',
            'fecha'=>$fecha,
            'resumen'=>$resumen
        ]);
    }
}

/* =====================================================
   Punto de entrada para rutas del fichaje
===================================================== */
$action = $_GET['action'] ?? '';
$controller = new FichajeController();

if ($action === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->registrar();
} elseif ($action === 'ultimos') {
    $controller->ultimosFichajes();
} elseif ($action === 'resumen') {
    $controller->resumenDiario();
} else {
    echo json_encode(['status'=>'error','message'=>'Acción no válida']);
}