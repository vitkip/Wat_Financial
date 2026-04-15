<?php
/**
 * Category Model
 */
class Category
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM categories ORDER BY name ASC"
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE id = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO categories (name, color, icon) VALUES (:name, :color, :icon)",
            [
                ':name'  => $data['name'],
                ':color' => $data['color'] ?? '#006C49',
                ':icon'  => $data['icon']  ?? 'tag',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE categories SET name = :name, color = :color, icon = :icon WHERE id = :id",
            [
                ':name'  => $data['name'],
                ':color' => $data['color'] ?? '#006C49',
                ':icon'  => $data['icon']  ?? 'tag',
                ':id'    => $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM categories WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getWithStats(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*,
                    COUNT(t.id)       AS tx_count,
                    COALESCE(SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END), 0) AS total_expense,
                    COALESCE(SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END), 0) AS total_income
             FROM categories c
             LEFT JOIN transactions t ON t.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC"
        );
    }
}
