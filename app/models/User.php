<?php

require_once __DIR__ . '/../../config/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* =====================================================
       Creamos un usuario
    ===================================================== */

    public function create(string $nombre, string $email, string $password, int $role_id): bool
    {
        if ($this->emailExists($email)) {
            return false; // el email introducido ya existe
        }

        $sql = "INSERT INTO users (nombre, email, password, role_id)
                VALUES (:nombre, :email, :password, :role_id)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre'   => $nombre,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role_id'  => $role_id
        ]);
    }

    /* =====================================================
       Obtener usuario por email
    ===================================================== */

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /* =====================================================
       Obtener usuario por ID
    ===================================================== */

    public function findById(int $id): ?array
    {
        $sql = "SELECT u.*, r.nombre AS role_nombre
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /* =====================================================
       Verificar si el email existe
    ===================================================== */

    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    /* =====================================================
       Verificamos el login
    ===================================================== */

    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        if (!$user['activo']) {
            return null;
        }

        return $user;
    }

    /* =====================================================
       OBTENER TODOS (ADMIN)
    ===================================================== */

    public function getAll(): array
    {
        $sql = "SELECT u.id, u.nombre, u.email, u.activo, r.nombre AS rol
                FROM users u
                JOIN roles r ON u.role_id = r.id
                ORDER BY u.created_at DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================================
       Cuenta Usuarios Activos
    ===================================================== */

    public function countUsuariosActivos(): int
    {
        return $this->db->query("SELECT COUNT(*) FROM users WHERE activo = 1")
                        ->fetchColumn();
    }

    /* =====================================================
       Obtener unicamente los empleados
    ===================================================== */

    public function getEmpleados(): array
    {
        $sql = "SELECT id, nombre 
                FROM users 
                WHERE role_id = 2 AND activo = 1
                ORDER BY nombre ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================================
       Comprueba si es Administrador
    ===================================================== */

    public function isAdmin(int $userId): bool
    {
        $sql = "SELECT role_id FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);

        return $stmt->fetchColumn() == 1;
    }

    /* =====================================================
       Actualizar usuario
    ===================================================== */

    public function update(int $id, string $nombre, string $email, int $role_id): bool
    {
        $sql = "UPDATE users 
                SET nombre = :nombre,
                    email = :email,
                    role_id = :role_id
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre'  => $nombre,
            ':email'   => $email,
            ':role_id' => $role_id,
            ':id'      => $id
        ]);
    }

    /* =====================================================
       Obtener rol de usuario
    ===================================================== */

    public function getUserRol(int $id): ?string
    {
        $sql = "SELECT r.nombre AS rol
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rol ? $rol['rol'] : null;
    }

    /* =====================================================
       Cambiar contraseña
    ===================================================== */

    public function updatePassword(int $id, string $newPassword): bool
    {
        $sql = "UPDATE users 
                SET password = :password
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id'       => $id
        ]);
    }

    /* =====================================================
       Activar o desactivar usuario
    ===================================================== */

    public function toggleActive(int $id, bool $activo): bool
    {
        $sql = "UPDATE users SET activo = :activo WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':activo' => $activo,
            ':id'     => $id
        ]);
    }

    /* =====================================================
       Eliminar usuario (no deberiamos de usarlo)
    ===================================================== */

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}