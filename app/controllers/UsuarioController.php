<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

enum roles: int{
    case Admin = 1;
    case Trabajador = 2;
}
class UsuarioController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();

        // Solo admins pueden acceder a este controlador
        if (!AuthController::checkAuth() || !AuthController::checkRole(roles::Admin->value)) {
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'Acceso denegado']);
            exit;
        }
    }

    /* =====================================================
       Listar todos los usuarios
       GET → devuelve JSON
    ===================================================== */
    public function index(): void
    {
        header('Content-Type: application/json');

        $usuarios = $this->userModel->getAll();

        echo json_encode([
            'status'=>'success',
            'usuarios'=>$usuarios
        ]);
    }

    public function listUsersJSON(): void
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');

        // Capturamos los filtros del GET
        $nombre = $_GET['nombre'] ?? '';
        $email  = $_GET['email'] ?? '';
        $rol    = $_GET['rol'] ?? '';

        // Llamamos a la nueva función del modelo
        $usuarios = $this->userModel->getFilteredUsers($nombre, $email, $rol);

        echo json_encode([
            'status' => 'success',
            'users' => $usuarios,
            'totalPages' => 1
        ]);
    }

    public function editarUsuarioJSON(): void
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? 0;
        $nombre = $data['nombre'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->userModel->updateUser($id, $nombre, $email, $password)) {
            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar']);
        }
    }

    public function toggleUsuarioJSON(): void
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? 0;

        $user = $this->userModel->findById($id);
        $nuevoEstado = ($user['activo'] == 1) ? 0 : 1;

        if ($this->userModel->setEstado($id, $nuevoEstado)) {
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al cambiar estado']);
        }
    }

    /* =====================================================
       Crear un nuevo usuario
       POST: nombre, email, password, role_id
       Devuelve JSON para SweetAlert
    ===================================================== */
    public function crear(): void
    {
        header('Content-Type: application/json');

        $nombre = $_POST['nombre'] ?? '';
        $email  = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role_id  = intval($_POST['role_id'] ?? roles::Cliente->value); // por defecto Cliente

        if (!$nombre || !$email || !$password) {
            echo json_encode(['status'=>'error','message'=>'Todos los campos son obligatorios']);
            exit;
        }

        if ($this->userModel->create($nombre, $email, $password, $role_id)) {
            echo json_encode(['status'=>'success','message'=>'Usuario creado correctamente']);
        } else {
            echo json_encode(['status'=>'error','message'=>'El email ya existe']);
        }
    }

    /* =====================================================
       Editar usuario
       POST: id, nombre, email, role_id
       Devuelve JSON
    ===================================================== */
    public function editar(): void
    {
        header('Content-Type: application/json');

        $id = intval($_POST['id'] ?? 0);
        $nombre = $_POST['nombre'] ?? '';
        $email  = $_POST['email'] ?? '';
        $role_id = intval($_POST['role_id'] ?? roles::Cliente->value);

        if (!$id || !$nombre || !$email) {
            echo json_encode(['status'=>'error','message'=>'Todos los campos son obligatorios']);
            exit;
        }

        if ($this->userModel->update($id, $nombre, $email, $role_id)) {
            echo json_encode(['status'=>'success','message'=>'Usuario actualizado correctamente']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Error al actualizar usuario']);
        }
    }

    /* =====================================================
       Activar / desactivar usuario
       POST: id, activo (0 o 1)
    ===================================================== */
    public function toggleActivo(): void
    {
        header('Content-Type: application/json');

        $id = intval($_POST['id'] ?? 0);
        $activo = isset($_POST['activo']) ? boolval($_POST['activo']) : true;

        if (!$id) {
            echo json_encode(['status'=>'error','message'=>'ID inválido']);
            exit;
        }

        if ($this->userModel->toggleActive($id, $activo)) {
            $msg = $activo ? 'Usuario activado' : 'Usuario desactivado';
            echo json_encode(['status'=>'success','message'=>$msg]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Error al cambiar estado']);
        }
    }

    /* =====================================================
       Eliminar usuario (opcional)
    ===================================================== */
    public function eliminar(): void
    {
        header('Content-Type: application/json');

        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status'=>'error','message'=>'ID inválido']);
            exit;
        }

        if ($this->userModel->delete($id)) {
            echo json_encode(['status'=>'success','message'=>'Usuario eliminado']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Error al eliminar usuario']);
        }
    }
}

$action = $_GET['action'] ?? '';
$usuarioController = new UsuarioController();

switch($action){
    case 'index':
        $usuarioController->index();
        break;
    case 'crear':
        $usuarioController->crear();
        break;
    case 'editar':
        $usuarioController->editar();
        break;
    case 'toggle':
        $usuarioController->toggleActivo();
        break;
    case 'eliminar':
        $usuarioController->eliminar();
        break;
    case 'listUsers':
        $usuarioController->listUsersJSON();
        break;
    case 'editarUsuario':
        $usuarioController->editarUsuarioJSON();
        break;
    case 'toggleUsuario':
        $usuarioController->toggleUsuarioJSON();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Accion no valida'
        ]);
        break;
}