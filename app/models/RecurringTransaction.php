<?php
class RecurringTransaction
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, c.name AS category_name, c.color AS category_color
             FROM recurring_transactions r
             LEFT JOIN categories c ON r.category_id = c.id
             ORDER BY r.is_active DESC, r.next_run ASC"
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT r.*, c.name AS category_name
             FROM recurring_transactions r
             LEFT JOIN categories c ON r.category_id = c.id
             WHERE r.id = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): int
    {
        $nextRun = $this->calcNextRun($data['frequency'], $data['start_date'], (int)($data['day_of_month'] ?? 1));
        $this->db->execute(
            "INSERT INTO recurring_transactions
                (type, amount, description, category_id, frequency, day_of_month, start_date, end_date, next_run, notes)
             VALUES (:type,:amount,:description,:category_id,:frequency,:day_of_month,:start_date,:end_date,:next_run,:notes)",
            [
                ':type'         => $data['type'],
                ':amount'       => $data['amount'],
                ':description'  => $data['description'],
                ':category_id'  => $data['category_id'] ?? null,
                ':frequency'    => $data['frequency'],
                ':day_of_month' => $data['day_of_month'] ?? null,
                ':start_date'   => $data['start_date'],
                ':end_date'     => $data['end_date'] ?? null, // ✅ ບັນທຶກ end_date
                ':next_run'     => $nextRun,
                ':notes'        => $data['notes'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function toggle(int $id): void
    {
        $this->db->execute(
            "UPDATE recurring_transactions SET is_active = NOT is_active WHERE id = :id",
            [':id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM recurring_transactions WHERE id = :id",
            [':id' => $id]
        );
    }

    public function updateNextRun(int $id, string $date): void
    {
        $this->db->execute(
            "UPDATE recurring_transactions SET last_run = CURDATE(), next_run = :next WHERE id = :id",
            [':next' => $date, ':id' => $id]
        );
    }

    public function calcNextRun(string $frequency, string $fromDate, int $dayOfMonth = 1): string
    {
        $dt = new DateTime($fromDate);
        return match ($frequency) {
            'daily'   => $dt->modify('+1 day')->format('Y-m-d'),
            'weekly'  => $dt->modify('+1 week')->format('Y-m-d'),
            'yearly'  => $dt->modify('+1 year')->format('Y-m-d'),
            default   => (function() use ($dt, $dayOfMonth) {
                $dt->modify('first day of next month');
                $max = (int)$dt->format('t');
                $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), min($dayOfMonth, $max));
                return $dt->format('Y-m-d');
            })(),
        };
    }
}