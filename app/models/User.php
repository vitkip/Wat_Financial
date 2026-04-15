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
            "SELECT id, name, email, avatar, is_active, last_login_at, created_at
             FROM users WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getByEmail(string $email): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = :email AND is_active = 1",
            [':email' => $email]
        );
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)",
            [
                ':name'     => $data['name'],
                ':email'    => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updatePassword(int $id, string $newPassword): int
    {
        return $this->db->execute(
            "UPDATE users SET password = :hash WHERE id = :id",
            [':hash' => password_hash($newPassword, PASSWORD_BCRYPT), ':id' => $id]
        );
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
            "SELECT id, name, email, is_active, last_login_at, created_at
             FROM users ORDER BY created_at DESC"
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