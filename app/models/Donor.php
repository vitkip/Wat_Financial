<?php
/**
 * Donor Model
 */
class Donor
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT d.*,
                    COALESCE(SUM(CASE WHEN t.status = 'approved' AND t.type = 'income' THEN t.amount ELSE 0 END), 0) AS total_donated,
                    COUNT(CASE WHEN t.status = 'approved' AND t.type = 'income' THEN t.id END) AS donation_count
             FROM donors d
             LEFT JOIN transactions t ON d.id = t.donor_id
             GROUP BY d.id
             ORDER BY d.name ASC"
        );
    }

    /** Lightweight variant — id + name + phone only, for select dropdowns. */
    public function getAllForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, phone FROM donors ORDER BY name ASC"
        );
    }

    public function search(string $query): array
    {
        $like = '%' . $query . '%';
        return $this->db->fetchAll(
            "SELECT d.*, 
                    COALESCE(SUM(CASE WHEN t.status = 'approved' THEN t.amount ELSE 0 END), 0) AS total_donated,
                    COUNT(CASE WHEN t.status = 'approved' THEN t.id ELSE NULL END) AS donation_count
             FROM donors d
             LEFT JOIN transactions t ON d.id = t.donor_id
             WHERE d.name LIKE :q OR d.phone LIKE :q2
             GROUP BY d.id
             ORDER BY d.name ASC
             LIMIT 20",
            [':q' => $like, ':q2' => $like]
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch("SELECT * FROM donors WHERE id = :id", [':id' => $id]);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO donors (name, phone, address) VALUES (:name, :phone, :address)",
            [
                ':name' => $data['name'],
                ':phone' => $data['phone'] ?? null,
                ':address' => $data['address'] ?? null
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE donors SET name = :name, phone = :phone, address = :address WHERE id = :id",
            [
                ':name' => $data['name'],
                ':phone' => $data['phone'] ?? null,
                ':address' => $data['address'] ?? null,
                ':id' => $id
            ]
        );
    }
}
