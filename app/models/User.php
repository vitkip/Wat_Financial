<?php
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT u.id, u.name, u.email, u.avatar, u.is_active,
                    u.last_login_at, u.created_at, u.role, u.role_id,
                    r.name AS role_name, r.label_lao AS role_label
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id",
            [':id' => $id]
        );
    }

    public function getByEmail(string $email): array|false
    {
        return $this->db->fetch(
            "SELECT u.*, r.name AS role_name, r.label_lao AS role_label
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1",
            [':email' => $email]
        );
    }

    public function create(array $data): int
    {
        // Accept either a role_id (new RBAC) or legacy role slug
        $roleId = !empty($data['role_id']) ? (int) $data['role_id'] : null;

        // If no role_id given, look up by slug for backward compat
        if ($roleId === null && !empty($data['role'])) {
            $row    = $this->db->fetch(
                "SELECT id FROM roles WHERE name = :n",
                [':n' => $data['role']]
            );
            $roleId = $row ? (int) $row['id'] : null;
        }

        $this->db->execute(
            "INSERT INTO users (name, email, password, role, role_id)
             VALUES (:name, :email, :password, :role, :role_id)",
            [
                ':name'     => $data['name'],
                ':email'    => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':role'     => $data['role'] ?? 'clerk',
                ':role_id'  => $roleId,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updatePassword(int $id, string $newPassword): int
    {
        return $this->db->execute(
            "UPDATE users SET password = :hash WHERE id = :id",
            [':hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), ':id' => $id]
        );
    }

    /** Assign a new role (updates both legacy slug and new role_id). */
    public function updateRole(int $id, int $roleId): int
    {
        $role = Database::getInstance()->fetch(
            "SELECT name FROM roles WHERE id = :rid",
            [':rid' => $roleId]
        );
        $affected = $this->db->execute(
            "UPDATE users SET role_id = :role_id, role = :role WHERE id = :id",
            [
                ':role_id' => $roleId,
                ':role'    => $role ? $role['name'] : 'viewer',
                ':id'      => $id,
            ]
        );
        Permission::flushUser($id);
        return $affected;
    }

    public function updateProfile(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE users SET name = :name, email = :email WHERE id = :id",
            [':name' => $data['name'], ':email' => $data['email'], ':id' => $id]
        );
    }

    public function touchLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.is_active, u.last_login_at,
                    u.created_at, u.role, u.role_id,
                    r.label_lao AS role_label, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC"
        );
    }

    public function toggleActive(int $id): int
    {
        return $this->db->execute(
            "UPDATE users SET is_active = 1 - is_active WHERE id = :id",
            [':id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM users WHERE id = :id",
            [':id' => $id]
        );
    }

    public function verify(string $email, string $password): array|false
    {
        $user = $this->getByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        return $user;
    }
}