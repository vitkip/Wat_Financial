<?php
/**
 * Transaction Model (V2 Architecture - Double Entry)
 */
class Transaction
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    u.name AS creator_name, d.name AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u ON t.created_by = u.id
             LEFT JOIN donors d ON t.donor_id = d.id
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
    }

    public function getRecent(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    u.name AS creator_name, d.name AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u ON t.created_by = u.id
             LEFT JOIN donors d ON t.donor_id = d.id
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit",
            [':limit' => $limit]
        );
    }

    public function getPending(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon, u.name AS creator_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u ON t.created_by = u.id
             WHERE t.status = 'pending'
             ORDER BY t.created_at ASC
             LIMIT :limit",
            [':limit' => $limit]
        );
    }

    public function search(array $filters, int $limit = 50, int $offset = 0): array
    {
        return $this->searchWithCount($filters, $limit, $offset)['rows'];
    }

    /**
     * Single query: returns paginated rows + total count via window function.
     * Eliminates the separate countFiltered() round-trip.
     */
    public function searchWithCount(array $filters, int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $rows = $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon,
                    u.name  AS creator_name,
                    d.name  AS donor_name,
                    COUNT(*) OVER() AS _total_count
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             {$where}
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $limit, ':offset' => $offset])
        );

        $total = empty($rows) ? 0 : (int) $rows[0]['_total_count'];

        // Strip the internal column before returning to views
        $rows = array_map(static function (array $r) {
            unset($r['_total_count']);
            return $r;
        }, $rows);

        return ['rows' => $rows, 'total' => $total];
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);

        // Only JOIN categories when a text search is active (avoids unnecessary join)
        $catJoin = !empty($filters['q'])
            ? "LEFT JOIN categories c ON t.category_id = c.id"
            : "";

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM transactions t {$catJoin} {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    private function buildWhere(array $f): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($f['q'])) {
            $conditions[] = "(t.description LIKE :q OR c.name LIKE :q2 OR t.reference_no LIKE :q3)";
            $like = '%' . $f['q'] . '%';
            $params[':q']  = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($f['type']) && in_array($f['type'], ['income', 'expense'], true)) {
            $conditions[] = "t.type = :type";
            $params[':type'] = $f['type'];
        }

        $validStatuses = ['draft', 'pending', 'approved', 'rejected'];
        if (!empty($f['status']) && in_array($f['status'], $validStatuses, true)) {
            $conditions[] = "t.status = :status";
            $params[':status'] = $f['status'];
        }

        if (!empty($f['category_id'])) {
            $conditions[] = "t.category_id = :category_id";
            $params[':category_id'] = (int) $f['category_id'];
        }

        if (!empty($f['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['date_from'])) {
            $conditions[] = "t.date >= :date_from";
            $params[':date_from'] = $f['date_from'];
        }
        if (!empty($f['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['date_to'])) {
            $conditions[] = "t.date <= :date_to";
            $params[':date_to'] = $f['date_to'];
        }

        if (is_numeric($f['amount_min'] ?? '')) {
            $conditions[] = "t.amount >= :amount_min";
            $params[':amount_min'] = (float) $f['amount_min'];
        }
        if (is_numeric($f['amount_max'] ?? '')) {
            $conditions[] = "t.amount <= :amount_max";
            $params[':amount_max'] = (float) $f['amount_max'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT t.*, c.name AS category_name, u.name as creator_name, a.name as approver_name, d.name as donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u ON t.created_by = u.id
             LEFT JOIN users a ON t.approved_by = a.id
             LEFT JOIN donors d ON t.donor_id = d.id
             WHERE t.id = :id",
            [':id' => $id]
        );
    }
    
    public function getLedgerEntries(int $transactionId): array
    {
        return $this->db->fetchAll(
            "SELECT l.*, a.name as account_name
             FROM ledger_entries l
             JOIN accounts a ON l.account_id = a.id
             WHERE l.transaction_id = :tx_id",
            [':tx_id' => $transactionId]
        );
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->execute(
                "INSERT INTO transactions (type, amount, description, category_id, date, notes, status, reference_no, created_by, donor_id)
                 VALUES (:type, :amount, :description, :category_id, :date, :notes, :status, :reference_no, :created_by, :donor_id)",
                [
                    ':type'        => $data['type'],
                    ':amount'      => $data['amount'],
                    ':description' => $data['description'],
                    ':category_id' => $data['category_id'] ?? null,
                    ':date'        => $data['date'],
                    ':notes'       => $data['notes'] ?? null,
                    ':status'      => $data['status'] ?? 'approved',
                    ':reference_no'=> $data['reference_no'] ?? null,
                    ':created_by'  => $data['created_by'] ?? null,
                    ':donor_id'    => $data['donor_id'] ?? null,
                ]
            );
            $txId = (int) $this->db->lastInsertId();

            if (!empty($data['account_id'])) {
                // If it's income, it increases asset (debit)
                // If it's expense, it decreases asset (credit)
                $entryType = ($data['type'] === 'income') ? 'debit' : 'credit';
                
                $this->db->execute(
                    "INSERT INTO ledger_entries (transaction_id, account_id, entry_type, amount)
                     VALUES (:tx_id, :acc_id, :entry_type, :amount)",
                    [
                        ':tx_id' => $txId,
                        ':acc_id' => $data['account_id'],
                        ':entry_type' => $entryType,
                        ':amount' => $data['amount']
                    ]
                );

                if (($data['status'] ?? 'approved') === 'approved') {
                    $accountModel = new Account();
                    $accountModel->recalculateBalance($data['account_id']);
                }
            }

            $this->db->commit();
            return $txId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function approve(int $id, int $adminId): bool
    {
        $this->db->beginTransaction();
        try {
            // Atomic: skip the prior getById() read entirely — the UPDATE itself acts as the
            // existence + status check. If the row is already approved/rejected (or missing),
            // affected rows will be 0 and we abort cleanly, preventing double-approval races.
            $affected = $this->db->execute(
                "UPDATE transactions SET status = 'approved', approved_by = :admin_id
                 WHERE id = :id AND status NOT IN ('approved', 'rejected')",
                [':admin_id' => $adminId, ':id' => $id]
            );

            if ($affected === 0) {
                $this->db->rollBack();
                return false;
            }

            $entries = $this->getLedgerEntries($id);
            $accountModel = new Account();
            foreach ($entries as $entry) {
                $accountModel->recalculateBalance($entry['account_id']);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function reject(int $id, int $adminId): bool
    {
        // Guard: cannot reject a transaction that is already finalised.
        return $this->db->execute(
            "UPDATE transactions SET status = 'rejected', approved_by = :admin_id
             WHERE id = :id AND status NOT IN ('approved', 'rejected')",
            [':admin_id' => $adminId, ':id' => $id]
        ) > 0;
    }

    public function update(int $id, array $data): int
    {
        $tx = $this->getById($id);
        if ($tx && $tx['status'] === 'approved') {
            if (!Permission::getInstance()->isSuperAdmin()) {
                throw new Exception("ບໍ່ສາມາດແກ້ໄຂທຸລະກຳທີ່ອະນຸມັດແລ້ວ.");
            }
        }

        return $this->db->execute(
            "UPDATE transactions SET
                description = :description, category_id = :category_id, date = :date, notes = :notes, donor_id = :donor_id
             WHERE id = :id",
            [
                ':description' => $data['description'],
                ':category_id' => $data['category_id'] ?? null,
                ':date'        => $data['date'],
                ':notes'       => $data['notes'] ?? null,
                ':donor_id'    => $data['donor_id'] ?? null,
                ':id'          => $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        $tx = $this->getById($id);
        if ($tx && $tx['status'] === 'approved') {
            if (!Permission::getInstance()->isSuperAdmin()) {
                throw new Exception("ບໍ່ສາມາດລຶບທຸລະກຳທີ່ອະນຸມັດແລ້ວ.");
            }
        }

        // ledger_entries will cascade delete
        $entries = $this->getLedgerEntries($id);
        
        $this->db->beginTransaction();
        try {
            $affected = $this->db->execute("DELETE FROM transactions WHERE id = :id", [':id' => $id]);
            
            // Recalculate affected accounts
            $accountModel = new Account();
            foreach ($entries as $entry) {
                $accountModel->recalculateBalance($entry['account_id']);
            }
            
            $this->db->commit();
            return $affected;
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    // ── Date range helpers ────────────────────────────────────────
    // All date-scoped queries use open-ended range predicates so that
    // MySQL can use the idx_tx_date_type_status index on (date, type, status).
    // DATE_FORMAT() / YEAR() wrap the column and defeat every index.

    private function monthRange(string $ym): array
    {
        $start = $ym . '-01';
        $end   = date('Y-m-d', strtotime('first day of next month', strtotime($start)));
        return [$start, $end];
    }

    private function yearRange(int $year): array
    {
        return ["{$year}-01-01", ($year + 1) . "-01-01"];
    }

    // ── Aggregates ────────────────────────────────────────────────

    /**
     * Single-query replacement for getTotalIncome() + getTotalExpenses().
     * Halves the number of DB round-trips on the dashboard.
     */
    public function getMonthSummary(string $month): array
    {
        [$start, $end] = $this->monthRange($month);
        $row = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expenses
             FROM transactions
             WHERE status = 'approved'
               AND date >= :start AND date < :end",
            [':start' => $start, ':end' => $end]
        );
        return [
            'income'   => (float) ($row['income']   ?? 0),
            'expenses' => (float) ($row['expenses'] ?? 0),
        ];
    }

    /** Kept for backwards compatibility — prefer getMonthSummary(). */
    public function getTotalIncome(?string $month = null): float
    {
        if ($month) return $this->getMonthSummary($month)['income'];
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions
             WHERE type = 'income' AND status = 'approved'"
        );
        return (float) ($row['total'] ?? 0.0);
    }

    public function getTotalExpenses(?string $month = null): float
    {
        if ($month) return $this->getMonthSummary($month)['expenses'];
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions
             WHERE type = 'expense' AND status = 'approved'"
        );
        return (float) ($row['total'] ?? 0.0);
    }

    public function getMonthlyTotals(int $months = 6): array
    {
        // date >= DATE_SUB(CURDATE(), ...) is already a range — index-friendly.
        // Use DATE_FORMAT only in SELECT/GROUP BY (not in WHERE), which is fine.
        return $this->db->fetchAll(
            "SELECT
                DATE_FORMAT(date, '%Y-%m') AS month,
                DATE_FORMAT(date, '%b %Y') AS label,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses
             FROM transactions
             WHERE status = 'approved'
               AND date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month ASC",
            [':months' => $months]
        );
    }

    public function getYearlyTotals(int $year): array
    {
        [$start, $end] = $this->yearRange($year);
        $rows = $this->db->fetchAll(
            "SELECT
                DATE_FORMAT(date, '%Y-%m') AS month,
                SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses
             FROM transactions
             WHERE status = 'approved'
               AND date >= :start AND date < :end
             GROUP BY DATE_FORMAT(date, '%Y-%m')",
            [':start' => $start, ':end' => $end]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['month']] = $row;
        }
        return $result;
    }

    public function getExpensesByCategory(?string $month = null, ?int $year = null): array
    {
        $sql    = "SELECT c.name AS category, c.color, c.icon, SUM(t.amount) AS total
                   FROM transactions t
                   LEFT JOIN categories c ON t.category_id = c.id
                   WHERE t.type = 'expense' AND t.status = 'approved'";
        $params = [];

        if ($month) {
            [$start, $end]  = $this->monthRange($month);
            $sql           .= " AND t.date >= :start AND t.date < :end";
            $params         = [':start' => $start, ':end' => $end];
        } elseif ($year) {
            [$start, $end]  = $this->yearRange($year);
            $sql           .= " AND t.date >= :start AND t.date < :end";
            $params         = [':start' => $start, ':end' => $end];
        }

        $sql .= " GROUP BY t.category_id ORDER BY total DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function count(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) AS cnt FROM transactions");
        return (int) ($row['cnt'] ?? 0);
    }

    public function searchAll(array $filters): array
    {
        [$where, $params] = $this->buildWhere($filters);
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    u.name AS creator_name, d.name AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             {$where}
             ORDER BY t.date DESC, t.id DESC",
            $params
        );
    }

    /**
     * Memory-safe streaming export — yields one row at a time via PDO cursor.
     * Use with foreach(); do NOT collect into an array.
     */
    public function cursorForExport(array $filters): PDOStatement
    {
        [$where, $params] = $this->buildWhere($filters);
        return $this->db->cursor(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    u.name AS creator_name, d.name AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             {$where}
             ORDER BY t.date DESC, t.id DESC",
            $params
        );
    }

    /** @deprecated Use cursorForExport() for large datasets. */
    public function getAllForExport(): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    u.name AS creator_name, d.name AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u ON t.created_by = u.id
             LEFT JOIN donors d ON t.donor_id = d.id
             WHERE t.status = 'approved'
             ORDER BY t.date DESC, t.id DESC"
        );
    }

    public function getByMonth(string $month): array
    {
        [$start, $end] = $this->monthRange($month);
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.status = 'approved'
               AND t.date >= :start AND t.date < :end
             ORDER BY t.date ASC, t.id ASC",
            [':start' => $start, ':end' => $end]
        );
    }

    public function getByYear(int $year): array
    {
        [$start, $end] = $this->yearRange($year);
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.status = 'approved'
               AND t.date >= :start AND t.date < :end
             ORDER BY t.date ASC, t.id ASC",
            [':start' => $start, ':end' => $end]
        );
    }
}
