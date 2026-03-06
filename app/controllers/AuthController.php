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
        // Limpieza de buffer para garantizar un JSON puro (evita errores de parseo en JS)
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Debes completar todos los campos'
            ]);
            exit;
        }

        // Buscamos al usuario en la base de datos
        $user = $this->userModel->findByEmail($email);

        // 1. Validar si el usuario existe y la contraseña es correcta
        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Email o contraseña incorrectos'
            ]);
            exit;
        }

        // 2. Validar si la cuenta está activa (activo == 1)
        if (!(int)$user['activo']) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Cuenta desactivada. Contacta con un administrador.'
            ]);
            exit;
        }

        // 3. Crear sesión si todo es correcto
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['role_id'] = $user['role_id'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Login correcto',
            'role'   => $user['role_id']
        ]);
        exit;
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