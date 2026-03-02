<?php

require_once __DIR__ . '/../../config/Database.php';

class Role
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance(); // usamos tu Database::getInstance()
    }

    /* =====================================================
       Obtener todos los roles
    ===================================================== */
    public function getAll(): array
    {
        $sql = "SELECT id, nombre FROM roles ORDER BY nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================================
       Obtener rol por id
    ===================================================== */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, nombre FROM roles WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rol ?: null;
    }

    /* =====================================================
       Comprobar si rol existe
    ===================================================== */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM roles WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    /* =====================================================
       Obtener nombre del rol por id
    ===================================================== */
    public function getName(int $id): ?string
    {
        $rol = $this->findById($id);
        return $rol ? $rol['nombre'] : null;
    }
}