<?php

require_once __DIR__ . '/../../config/Database.php';
class EstadoUsuario {
 private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    
    //Obtener el estado del usuario
     
    public function getEstado($userId) {
        $stmt = $this->db->prepare("SELECT tipo_ultimo, segundos_actuales FROM estado_usuario WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $estado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no existe, creamos un registro inicial
        if (!$estado) {
            $this->crearEstado($userId);
            return ['tipo_ultimo' => 'ninguno', 'segundos_actuales' => 0];
        }

        return $estado;
    }

 
    //Crear un estado inicial para el usuario
 
    public function crearEstado($userId) {
        $stmt = $this->db->prepare("
            INSERT INTO estado_usuario (user_id, tipo_ultimo, segundos_actuales)
            VALUES (:user_id, 'ninguno', 0)
        ");
        $stmt->execute(['user_id' => $userId]);
    }

   
    //Actualizar segundos actuales del usuario
   
    public function actualizarSegundos($userId, $segundos) {
        $stmt = $this->db->prepare("
            UPDATE estado_usuario
            SET segundos_actuales = :segundos
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            'segundos' => $segundos,
            'user_id' => $userId
        ]);
    }

 
    //Actualizar tipo de fichaje y opcionalmente resetear segundos
  
    public function actualizarTipo($userId, $tipo, $resetSegundos = false) {
        if ($resetSegundos) {
            $stmt = $this->db->prepare("
                UPDATE estado_usuario
                SET tipo_ultimo = :tipo, segundos_actuales = 0
                WHERE user_id = :user_id
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE estado_usuario
                SET tipo_ultimo = :tipo
                WHERE user_id = :user_id
            ");
        }

        $stmt->execute([
            'tipo' => $tipo,
            'user_id' => $userId
        ]);
    }
}