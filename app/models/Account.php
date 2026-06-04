<?php
/**
 * Account Model
 */
class Account
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM accounts ORDER BY type, name");
    }

    public function getActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM accounts WHERE is_active = 1 ORDER BY type, name");
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch("SELECT * FROM accounts WHERE id = :id", [':id' => $id]);
    }

    public function recalculateBalance(int $id): float
    {
        // Lock the accounts row for the duration of the caller's transaction so that
        // two concurrent approvals targeting the same account cannot interleave their
        // read-then-write and produce a stale balance. FOR UPDATE is a no-op outside
        // an explicit transaction, so this is safe to call from anywhere.
        $account = $this->db->fetch(
            "SELECT * FROM accounts WHERE id = :id FOR UPDATE",
            [':id' => $id]
        );
        if (!$account) return 0.0;

        $sql = "SELECT 
                    SUM(CASE WHEN l.entry_type = 'debit' THEN l.amount ELSE 0 END) as total_debit,
                    SUM(CASE WHEN l.entry_type = 'credit' THEN l.amount ELSE 0 END) as total_credit
                FROM ledger_entries l
                JOIN transactions t ON l.transaction_id = t.id
                WHERE l.account_id = :id AND t.status = 'approved'";
                
        $result = $this->db->fetch($sql, [':id' => $id]);
        
        $debit = (float)($result['total_debit'] ?? 0);
        $credit = (float)($result['total_credit'] ?? 0);
        
        $balance = 0.0;
        if (in_array($account['type'], ['asset', 'expense'])) {
            $balance = $debit - $credit;
        } else {
            $balance = $credit - $debit;
        }

        $this->db->execute("UPDATE accounts SET balance = :balance WHERE id = :id", [
            ':balance' => $balance,
            ':id' => $id
        ]);

        return $balance;
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO accounts (name, type, description, is_active) VALUES (:name, :type, :description, :is_active)",
            [
                ':name' => $data['name'],
                ':type' => $data['type'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE accounts SET name = :name, type = :type, description = :description, is_active = :is_active WHERE id = :id",
            [
                ':name' => $data['name'],
                ':type' => $data['type'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $id
            ]
        );
    }
}
