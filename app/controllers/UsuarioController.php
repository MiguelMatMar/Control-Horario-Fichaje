<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

enum roles: int{
    case Admin = 1;
    case Trabajador = 2;
    case Cliente = 3;
}
class UserController
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

/* =====================================================
   Punto de entrada para rutas de usuario
===================================================== */
$action = $_GET['action'] ?? '';
$controller = new UserController();

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'crear':
        $controller->crear();
        break;
    case 'editar':
        $controller->editar();
        break;
    case 'toggle':
        $controller->toggleActivo();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status'=>'error','message'=>'Acción no válida']);
        break;
}