<?php
class Database {
    private static $instance = null;
    private $pdo;

    public function __construct() {
        $host = 'pruebadb.cnqg4w88uem0.eu-north-1.rds.amazonaws.com';
        $db   = 'control_horario';
        $user = 'admin';
        $pass = '13122005Migue!';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }

    public function test(){
        if($this -> pdo){
            return "Todo correcto";
        }
    }
}
