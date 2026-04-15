<?php
class SavingsGoal
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM savings_goals ORDER BY is_completed ASC, target_date ASC, created_at DESC"
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM savings_goals WHERE id = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO savings_goals (name, description, target_amount, current_amount, target_date, color, icon)
             VALUES (:name,:description,:target_amount,:current_amount,:target_date,:color,:icon)",
            [
                ':name'           => $data['name'],
                ':description'    => $data['description']    ?? null,
                ':target_amount'  => $data['target_amount'],
                ':current_amount' => $data['current_amount'] ?? 0,
                ':target_date'    => !empty($data['target_date']) ? $data['target_date'] : null,
                ':color'          => $data['color']          ?? '#006C49',
                ':icon'           => $data['icon']           ?? 'target',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function addAmount(int $id, float $amount): int
    {
        $rows = $this->db->execute(
            "UPDATE savings_goals
             SET current_amount = LEAST(current_amount + :amount, target_amount),
                 is_completed   = IF(current_amount + :amount2 >= target_amount, 1, is_completed)
             WHERE id = :id",
            [':amount' => $amount, ':amount2' => $amount, ':id' => $id]
        );
        return $rows;
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM savings_goals WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getTotalSaved(): float
    {
        $row = $this->db->fetch("SELECT COALESCE(SUM(current_amount),0) AS total FROM savings_goals WHERE is_completed = 0");
        return (float)($row['total'] ?? 0);
    }
}