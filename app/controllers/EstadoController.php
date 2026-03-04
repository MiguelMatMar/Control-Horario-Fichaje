<?php
require_once __DIR__ . '/../models/estadoUsuario.php';

class EstadoController {
    private EstadoUsuario $estadoModel;

    public function __construct() {
        $this->estadoModel = new EstadoUsuario();
    }

    // Acción: obtener estado actual del usuario
    public function getEstado() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $estado = $this->estadoModel->getEstado($userId);

        echo json_encode([
            'status' => 'success',
            'tipo' => $estado['tipo_ultimo'],
            'segundos' => (int)$estado['segundos_actuales']
        ]);
    }

    // Acción: actualizar estado del usuario
    public function updateEstado() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $tipo = $_POST['tipo'] ?? 'ninguno';
        $segundos = isset($_POST['segundos']) ? (int)$_POST['segundos'] : 0;

        $this->estadoModel->actualizarTipo($userId, $tipo);
        $this->estadoModel->actualizarSegundos($userId, $segundos);

        echo json_encode(['status' => 'success']);
    }
}

//Ruteo simple
$action = $_GET['action'] ?? '';

$controller = new EstadoController();

switch($action) {
    case 'getEstado':
        $controller->getEstado();
        break;
    case 'updateEstado':
        $controller->updateEstado();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}