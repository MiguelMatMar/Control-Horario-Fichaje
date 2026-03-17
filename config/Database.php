<?php
class Database {
    private static $instance = null;
    private $pdo;

    public function __construct() {
        // Datos de tu captura de Aiven
        $host = 'mysql-272af4aa-carlosmumo1717.k.aivencloud.com';
        $port = '20618'; // Tu puerto específico de Aiven
        $db   = 'control_horario'; 
        $user = 'avnadmin';
        $pass = 'AVNS_62-zJb8yC1sBMbC3eYn';
        $charset = 'utf8mb4';

        // EL CAMBIO ESTÁ AQUÍ: Se añade port=$port después del host
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Cambiamos 'true' por este modo para evitar el error de verificación local
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Configuramos la cabecera para que el navegador sepa que es JSON
            header('Content-Type: application/json');
            // Enviamos el error real en un formato que el JS pueda leer
            echo json_encode([
                "status" => "error",
                "message" => "Fallo de conexión: " . $e->getMessage()
            ]);
            exit; // Detenemos la ejecución
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }

    public function test(){
        return ($this->pdo) ? "Todo correcto" : "Error";
    }
}