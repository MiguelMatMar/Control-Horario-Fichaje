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
    ===================================================== */
    public function registrar(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión']);
            exit;
        }

        $tipo = $_POST['tipo'] ?? '';
        $userId = $_SESSION['user_id'];

        try {
            $this->fichajeModel->registrar($userId, $tipo);

            echo json_encode([
                'status' => 'success',
                'message' => "Fichaje '$tipo' registrado correctamente"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /* =====================================================
       Obtener últimos fichajes del usuario (para la tabla)
    ===================================================== */
    public function ultimosFichajes(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        // Traemos fichajes de los últimos 7 días para el historial
        $fichajes = $this->fichajeModel->getFichajes($userId, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));

        echo json_encode([
            'status' => 'success',
            'fichajes' => $fichajes
        ]);
    }

    /* =====================================================
       Resumen Completo (Hoy, Semana, Mes, Histórico)
       Este es el que alimenta las tarjetas visuales
    ===================================================== */
    public function resumenCompleto(): void
    {
        header('Content-Type: application/json');

        if (!AuthController::checkAuth()) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión expirada']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $hoy = date('Y-m-d');

        try {
            // 1. Datos de Hoy
            $resumenHoy = $this->fichajeModel->calcularHorasPorFecha($userId, $hoy);
            
            // 2. Datos Semanales
            $semanal = $this->fichajeModel->resumenSemanal($userId, $hoy);
            
            // 3. Datos Mensuales
            $mensual = $this->fichajeModel->resumenMensual($userId, $hoy);
            
            // 4. Datos Históricos (Todo el tiempo)
            // Asegúrate de tener el método totalHistorico en tu modelo Fichaje.php
            $historico = $this->fichajeModel->totalHistorico($userId);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'hoy' => [
                        'total_horas_trabajadas' => $resumenHoy['horas_trabajadas'] ?? '0.00'
                    ],
                    'semanal' => [
                        'total_horas_trabajadas' => $semanal['total_horas_trabajadas'] ?? '0.00'
                    ],
                    'mensual' => [
                        'total_horas_trabajadas' => $mensual['total_horas_trabajadas'] ?? '0.00'
                    ],
                    'historico' => [
                        'total_horas_trabajadas' => $historico['total_horas_trabajadas'] ?? '0.00'
                    ]
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al calcular resúmenes: ' . $e->getMessage()
            ]);
        }
    }

    // Método individual por si se necesita solo un día
    public function resumenDiario(): void
    {
        header('Content-Type: application/json');
        if (!AuthController::checkAuth()) {
            echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión']);
            exit;
        }
        $userId = $_SESSION['user_id'];
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $resumen = $this->fichajeModel->calcularHorasPorFecha($userId, $fecha);
        echo json_encode(['status' => 'success', 'resumen' => $resumen]);
    }
}

/* =====================================================
   PUNTO DE ENTRADA (RUTEO)
===================================================== */
$action = $_GET['action'] ?? '';
$controller = new FichajeController();

switch ($action) {
    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registrar();
        }
        break;

    case 'ultimos':
        $controller->ultimosFichajes();
        break;

    case 'resumen':
        $controller->resumenDiario();
        break;

    case 'resumen_completo':
        $controller->resumenCompleto();
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        break;
}