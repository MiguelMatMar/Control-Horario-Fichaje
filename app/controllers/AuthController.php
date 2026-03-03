<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /* =====================================================
       Login de usuario
       Recibe POST: email, password
       Devuelve JSON para SweetAlert
    ===================================================== */
    public function login(): void
    {
        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Debes completar todos los campos'
            ]);
            exit;
        }

        $user = $this->userModel->verifyPassword($email, $password);

        if (!$user) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email o contraseña incorrectos, o usuario inactivo'
            ]);
            exit;
        }

        // Guardar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['role_id'] = $user['role_id'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Login correcto',
            'role'   => $user['role_id'] // opcional para redirección frontend
        ]);
    }

    /* =====================================================
       Logout de usuario
       Destruye sesión y redirige a login
    ===================================================== */
    public function logout(): void
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }
        session_unset();
        session_destroy();

        header('Content-Type: application/json');
        echo json_encode(['status'=>'success','message'=>'Has cerrado sesión']);
        exit;
    }

    /* =====================================================
       Método auxiliar para verificar sesión activa
    ===================================================== */
    public static function checkAuth(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /* =====================================================
       Método auxiliar para verificar rol
       $rolEsperado: int (1=Admin, 2=Trabajador, 3=Cliente)
    ===================================================== */
    public static function checkRole(int $rolEsperado): bool
    {
        return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $rolEsperado;
    }
}

/* =====================================================
   Punto de entrada para login/logout vía POST o GET
   Ajusta las rutas según tu estructura web
===================================================== */
$action = $_GET['action'] ?? '';

$auth = new AuthController();

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
} elseif ($action === 'logout') {
    $auth->logout();
}