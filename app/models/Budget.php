<?php
/**
 * Budget Model
 */
class Budget
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?string $month = null): array
    {
        $month = $month ?? date('Y-m');
        $start = $month . '-01';
        $end   = date('Y-m-d', strtotime('first day of next month', strtotime($start)));

        return $this->db->fetchAll(
            "SELECT
                b.*,
                c.name    AS category_name,
                c.color   AS category_color,
                c.icon    AS category_icon,
                COALESCE(SUM(t.amount), 0) AS spent
             FROM budgets b
             LEFT JOIN categories c ON b.category_id = c.id
             LEFT JOIN transactions t
                ON t.category_id = b.category_id
                AND t.type       = 'expense'
                AND t.status     = 'approved'
                AND t.date      >= :start
                AND t.date       < :end
             WHERE b.month = :month
             GROUP BY b.id
             ORDER BY spent DESC",
            [':start' => $start, ':end' => $end, ':month' => $month]
        );
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO budgets (category_id, amount, month)
             VALUES (:category_id, :amount, :month)
             ON DUPLICATE KEY UPDATE amount = :amount2",
            [
                ':category_id' => $data['category_id'],
                ':amount'      => $data['amount'],
                ':month'       => $data['month'],
                ':amount2'     => $data['amount'],
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM budgets WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getTotalBudgeted(?string $month = null): float
    {
        $month = $month ?? date('Y-m');
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM budgets WHERE month = :month",
            [':month' => $month]
        );
        return (float) ($row['total'] ?? 0);
    }
}
