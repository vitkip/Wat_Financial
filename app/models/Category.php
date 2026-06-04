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

    /**
     * Get categories filtered by transaction type.
     * Used to populate dropdowns that only show relevant categories.
     */
    public function getByType(string $txType): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE type = :type OR type = 'both' ORDER BY name ASC",
            [':type' => $txType]
        );
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO categories (name, color, icon, type) VALUES (:name, :color, :icon, :type)",
            [
                ':name'  => $data['name'],
                ':color' => $data['color'] ?? '#006C49',
                ':icon'  => $data['icon']  ?? 'tag',
                ':type'  => $data['type']  ?? 'both',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE categories SET name = :name, color = :color, icon = :icon, type = :type WHERE id = :id",
            [
                ':name'  => $data['name'],
                ':color' => $data['color'] ?? '#006C49',
                ':icon'  => $data['icon']  ?? 'tag',
                ':type'  => $data['type']  ?? 'both',
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

    /**
     * @param int|null $year  Scope stats to a single year (recommended).
     *                        NULL scans all history — avoid on large datasets.
     */
    public function getWithStats(?int $year = null): array
    {
        $dateFilter = '';
        $params     = [];

        if ($year !== null) {
            $dateFilter = 'AND t.date >= :start AND t.date < :end';
            $params     = [':start' => "{$year}-01-01", ':end' => ($year + 1) . "-01-01"];
        }

        return $this->db->fetchAll(
            "SELECT c.*,
                    COUNT(t.id) AS tx_count,
                    COALESCE(SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END), 0) AS total_expense,
                    COALESCE(SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END), 0) AS total_income
             FROM categories c
             LEFT JOIN transactions t
                ON t.category_id = c.id
               AND t.status = 'approved'
               {$dateFilter}
             GROUP BY c.id
             ORDER BY c.name ASC",
            $params
        );
    }
}
