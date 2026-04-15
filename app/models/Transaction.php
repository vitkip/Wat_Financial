<?php
/**
 * Transaction Model
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
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
    }

    /**
     * ຄົ້ນຫາ ແລະ Filter ລາຍການ
     *
     * @param array  $filters  Keys: q, type, category_id, date_from, date_to, amount_min, amount_max
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function search(array $filters, int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             {$where}
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $limit, ':offset' => $offset])
        );
    }

    /**
     * ນັບຈຳນວນ rows ທີ່ຜ່ານ filter (ສຳລັບ pagination)
     */
    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Build WHERE clause + params ຈາກ filter array
     * @return array [string $whereClause, array $params]
     */
    private function buildWhere(array $f): array
    {
        $conditions = [];
        $params     = [];

        // ── Search text (description OR category name) ─────────────
        if (!empty($f['q'])) {
            $conditions[] = "(t.description LIKE :q OR c.name LIKE :q2)";
            $like = '%' . $f['q'] . '%';
            $params[':q']  = $like;
            $params[':q2'] = $like;
        }

        // ── Type ───────────────────────────────────────────────────
        if (!empty($f['type']) && in_array($f['type'], ['income', 'expense'], true)) {
            $conditions[] = "t.type = :type";
            $params[':type'] = $f['type'];
        }

        // ── Category ───────────────────────────────────────────────
        if (!empty($f['category_id'])) {
            $conditions[] = "t.category_id = :category_id";
            $params[':category_id'] = (int) $f['category_id'];
        }

        // ── Date range ─────────────────────────────────────────────
        if (!empty($f['date_from'])) {
            $conditions[] = "t.date >= :date_from";
            $params[':date_from'] = $f['date_from'];
        }
        if (!empty($f['date_to'])) {
            $conditions[] = "t.date <= :date_to";
            $params[':date_to'] = $f['date_to'];
        }

        // ── Amount range ───────────────────────────────────────────
        if (isset($f['amount_min']) && $f['amount_min'] !== '') {
            $conditions[] = "t.amount >= :amount_min";
            $params[':amount_min'] = (float) $f['amount_min'];
        }
        if (isset($f['amount_max']) && $f['amount_max'] !== '') {
            $conditions[] = "t.amount <= :amount_max";
            $params[':amount_max'] = (float) $f['amount_max'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    /**
     * ດຶງລາຍການທັງໝົດທີ່ຜ່ານ filter ໂດຍບໍ່ມີ pagination (ສຳລັບ export)
     */
    public function searchAll(array $filters): array
    {
        [$where, $params] = $this->buildWhere($filters);

        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             {$where}
             ORDER BY t.date DESC, t.id DESC",
            $params
        );
    }

    /**
     * ດຶງລາຍການທັງໝົດ ໂດຍບໍ່ filter (ສຳລັບ export ທຸລະກຳທັງໝົດ)
     */
    public function getAllForExport(): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             ORDER BY t.date DESC, t.id DESC"
        );
    }

    /**
     * ດຶງລາຍການຕາມເດືອນ (ສຳລັບ monthly report export)
     */
    public function getByMonth(string $month): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE DATE_FORMAT(t.date, '%Y-%m') = :month
             ORDER BY t.date ASC, t.id ASC",
            [':month' => $month]
        );
    }

    /**
     * ດຶງລາຍການຕາມປີ (ສຳລັບ yearly report export)
     */
    public function getByYear(int $year): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE YEAR(t.date) = :year
             ORDER BY t.date ASC, t.id ASC",
            [':year' => $year]
        );
    }

    public function getRecent(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             ORDER BY t.date DESC, t.id DESC
             LIMIT :limit",
            [':limit' => $limit]
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT t.*, c.name AS category_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.id = :id",
            [':id' => $id]
        );
    }

    public function getTotalIncome(string $month = null): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE type = 'income'";
        $params = [];
        if ($month) {
            $sql .= " AND DATE_FORMAT(date, '%Y-%m') = :month";
            $params[':month'] = $month;
        }
        return (float) ($this->db->fetch($sql, $params)['total'] ?? 0.0);
    }

    public function getTotalExpenses(string $month = null): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE type = 'expense'";
        $params = [];
        if ($month) {
            $sql .= " AND DATE_FORMAT(date, '%Y-%m') = :month";
            $params[':month'] = $month;
        }
        return (float) ($this->db->fetch($sql, $params)['total'] ?? 0.0);
    }

    public function getMonthlyTotals(int $months = 6): array
    {
        return $this->db->fetchAll(
            "SELECT
                DATE_FORMAT(date, '%Y-%m') AS month,
                DATE_FORMAT(date, '%b %Y') AS label,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END)  AS income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses
             FROM transactions
             WHERE date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month ASC",
            [':months' => $months]
        );
    }

    public function getExpensesByCategory(string $month = null, int $year = null): array
    {
        $sql = "SELECT
                    c.name  AS category,
                    c.color AS color,
                    c.icon  AS icon,
                    SUM(t.amount) AS total
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.type = 'expense'";
        $params = [];
        if ($month) {
            $sql .= " AND DATE_FORMAT(t.date, '%Y-%m') = :month";
            $params[':month'] = $month;
        } elseif ($year) {
            $sql .= " AND YEAR(t.date) = :year";
            $params[':year'] = $year;
        }
        $sql .= " GROUP BY t.category_id ORDER BY total DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO transactions (type, amount, description, category_id, date, notes)
             VALUES (:type, :amount, :description, :category_id, :date, :notes)",
            [
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':description' => $data['description'],
                ':category_id' => $data['category_id'] ?? null,
                ':date'        => $data['date'],
                ':notes'       => $data['notes'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE transactions SET
                type = :type, amount = :amount, description = :description,
                category_id = :category_id, date = :date, notes = :notes
             WHERE id = :id",
            [
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':description' => $data['description'],
                ':category_id' => $data['category_id'] ?? null,
                ':date'        => $data['date'],
                ':notes'       => $data['notes'] ?? null,
                ':id'          => $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM transactions WHERE id = :id",
            [':id' => $id]
        );
    }

    public function count(): int
    {
        return (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM transactions")['cnt'] ?? 0);
    }
}
