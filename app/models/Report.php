<?php
/**
 * Report Model — Centralised query layer for all financial reports.
 *
 * INTEGRITY RULE: Every public method returns the raw detail rows.
 * Totals are always computed by the caller (Controller or view) from those
 * rows using PHP's array_sum/array_column. This guarantees that header
 * totals can never diverge from the individual line items.
 *
 * All queries:
 *   • Filter WHERE status = 'approved'
 *   • Use half-open range predicates (date >= :start AND date < :end)
 *     so MySQL can use the idx_tx_date_type_status composite index.
 *   • Never use DATE_FORMAT(), YEAR(), or MONTH() in WHERE clauses.
 */
class Report
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Date helpers ──────────────────────────────────────────────────

    public function monthRange(string $ym): array
    {
        $start = $ym . '-01';
        $end   = date('Y-m-d', strtotime('first day of next month', strtotime($start)));
        return [$start, $end];
    }

    public function yearRange(int $year): array
    {
        return ["{$year}-01-01", ($year + 1) . "-01-01"];
    }

    public function dateRange(string $from, string $to): array
    {
        // Normalise: end date is inclusive, so add 1 day for open-ended range
        $end = date('Y-m-d', strtotime($to . ' +1 day'));
        return [$from, $end];
    }

    // ── Totals helper (PHP-computed, always consistent with rows) ─────

    /**
     * Compute income/expense totals from an array of transaction rows.
     * Use this instead of a separate DB COUNT/SUM to guarantee consistency.
     */
    public static function totalsFromRows(array $rows): array
    {
        $income  = 0.0;
        $expense = 0.0;
        foreach ($rows as $r) {
            if ($r['type'] === 'income')  $income  += (float) $r['amount'];
            else                          $expense += (float) $r['amount'];
        }
        return [
            'income'   => $income,
            'expense'  => $expense,
            'net'      => $income - $expense,
            'count'    => count($rows),
        ];
    }

    // ── 1. Daily Report ───────────────────────────────────────────────

    /**
     * Returns all approved transactions for a single calendar day,
     * ordered chronologically, with category and donor info.
     */
    public function getDaily(string $date): array
    {
        $end = date('Y-m-d', strtotime($date . ' +1 day'));
        return $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon,
                    u.name  AS creator_name,
                    d.name  AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             WHERE t.status = 'approved'
               AND t.date >= :date AND t.date < :end
             ORDER BY t.id ASC",
            [':date' => $date, ':end' => $end]
        );
    }

    // ── 2. Monthly Report ─────────────────────────────────────────────

    /** All approved transactions in a month, plus a daily breakdown. */
    public function getMonthly(string $month): array
    {
        [$start, $end] = $this->monthRange($month);
        $rows = $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon,
                    u.name  AS creator_name,
                    d.name  AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             WHERE t.status = 'approved'
               AND t.date >= :start AND t.date < :end
             ORDER BY t.date ASC, t.id ASC",
            [':start' => $start, ':end' => $end]
        );

        // Daily breakdown — derived from the same $rows (no extra query)
        $daily = [];
        foreach ($rows as $tx) {
            $d = $tx['date'];
            if (!isset($daily[$d])) {
                $daily[$d] = ['date' => $d, 'income' => 0.0, 'expense' => 0.0, 'count' => 0];
            }
            if ($tx['type'] === 'income') $daily[$d]['income']  += (float) $tx['amount'];
            else                          $daily[$d]['expense'] += (float) $tx['amount'];
            $daily[$d]['count']++;
        }

        // Category breakdown — also derived from $rows
        $byCat = [];
        foreach ($rows as $tx) {
            $key = $tx['category_name'] ?? 'ບໍ່ລະບຸໝວດ';
            if (!isset($byCat[$key])) {
                $byCat[$key] = [
                    'name'    => $key,
                    'color'   => $tx['category_color'] ?? '#6b7280',
                    'income'  => 0.0,
                    'expense' => 0.0,
                ];
            }
            if ($tx['type'] === 'income') $byCat[$key]['income']  += (float) $tx['amount'];
            else                          $byCat[$key]['expense'] += (float) $tx['amount'];
        }
        usort($byCat, fn($a, $b) => ($b['income'] + $b['expense']) <=> ($a['income'] + $a['expense']));

        return [
            'rows'     => $rows,
            'daily'    => array_values($daily),
            'by_cat'   => array_values($byCat),
            'totals'   => self::totalsFromRows($rows),
        ];
    }

    // ── 3. Annual Report ──────────────────────────────────────────────

    /** All approved transactions for a year, plus monthly and category breakdowns. */
    public function getAnnual(int $year): array
    {
        [$start, $end] = $this->yearRange($year);
        $rows = $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon,
                    u.name  AS creator_name,
                    d.name  AS donor_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             WHERE t.status = 'approved'
               AND t.date >= :start AND t.date < :end
             ORDER BY t.date ASC, t.id ASC",
            [':start' => $start, ':end' => $end]
        );

        // Seed all 12 months
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $mk = sprintf('%04d-%02d', $year, $m);
            $monthly[$mk] = ['month' => $mk, 'label' => laoMonthFull($m), 'income' => 0.0, 'expense' => 0.0];
        }
        foreach ($rows as $tx) {
            $mk = substr($tx['date'], 0, 7);
            if (isset($monthly[$mk])) {
                if ($tx['type'] === 'income') $monthly[$mk]['income']  += (float) $tx['amount'];
                else                          $monthly[$mk]['expense'] += (float) $tx['amount'];
            }
        }

        // Category expense breakdown
        $expByCat = [];
        foreach ($rows as $tx) {
            if ($tx['type'] !== 'expense') continue;
            $key = $tx['category_name'] ?? 'ບໍ່ລະບຸໝວດ';
            if (!isset($expByCat[$key])) {
                $expByCat[$key] = ['name' => $key, 'color' => $tx['category_color'] ?? '#6b7280', 'total' => 0.0];
            }
            $expByCat[$key]['total'] += (float) $tx['amount'];
        }
        usort($expByCat, fn($a, $b) => $b['total'] <=> $a['total']);

        return [
            'rows'      => $rows,
            'monthly'   => array_values($monthly),
            'exp_by_cat'=> array_values($expByCat),
            'totals'    => self::totalsFromRows($rows),
        ];
    }

    // ── 4. Donation Report ────────────────────────────────────────────

    /**
     * Income transactions in a date range, grouped by donor.
     * $from / $to are inclusive YYYY-MM-DD strings.
     */
    public function getDonations(string $from, string $to): array
    {
        [$start, $end] = $this->dateRange($from, $to);

        $rows = $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    d.name  AS donor_name,
                    d.phone AS donor_phone
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN donors d     ON t.donor_id    = d.id
             WHERE t.status = 'approved'
               AND t.type   = 'income'
               AND t.date  >= :start AND t.date < :end
             ORDER BY t.amount DESC, t.date ASC",
            [':start' => $start, ':end' => $end]
        );

        // Group by donor — derived from rows (no extra query)
        $byDonor = [];
        $noDonorKey = '__no_donor__';
        foreach ($rows as $tx) {
            $key = ($tx['donor_id'] ?? null) ? "d{$tx['donor_id']}" : $noDonorKey;
            if (!isset($byDonor[$key])) {
                $byDonor[$key] = [
                    'donor_id'    => $tx['donor_id'] ?? null,
                    'donor_name'  => $tx['donor_name'] ?? 'ບໍ່ລະບຸຊື່ (ທານ)',
                    'donor_phone' => $tx['donor_phone'] ?? '',
                    'total'       => 0.0,
                    'count'       => 0,
                    'last_date'   => $tx['date'],
                ];
            }
            $byDonor[$key]['total'] += (float) $tx['amount'];
            $byDonor[$key]['count']++;
            if ($tx['date'] > $byDonor[$key]['last_date']) {
                $byDonor[$key]['last_date'] = $tx['date'];
            }
        }
        usort($byDonor, fn($a, $b) => $b['total'] <=> $a['total']);

        // By category
        $byCat = [];
        foreach ($rows as $tx) {
            $key = $tx['category_name'] ?? 'ບໍ່ລະບຸໝວດ';
            if (!isset($byCat[$key])) {
                $byCat[$key] = ['name' => $key, 'color' => $tx['category_color'] ?? '#006C49', 'total' => 0.0, 'count' => 0];
            }
            $byCat[$key]['total'] += (float) $tx['amount'];
            $byCat[$key]['count']++;
        }
        usort($byCat, fn($a, $b) => $b['total'] <=> $a['total']);

        return [
            'rows'     => $rows,
            'by_donor' => array_values($byDonor),
            'by_cat'   => array_values($byCat),
            'totals'   => self::totalsFromRows($rows),
        ];
    }

    // ── 5. Expense Report ─────────────────────────────────────────────

    /** Expense transactions in a date range, broken down by category. */
    public function getExpenses(string $from, string $to): array
    {
        [$start, $end] = $this->dateRange($from, $to);

        $rows = $this->db->fetchAll(
            "SELECT t.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon,
                    u.name  AS creator_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN users u      ON t.created_by  = u.id
             WHERE t.status = 'approved'
               AND t.type   = 'expense'
               AND t.date  >= :start AND t.date < :end
             ORDER BY t.amount DESC, t.date ASC",
            [':start' => $start, ':end' => $end]
        );

        // Category breakdown
        $byCat = [];
        foreach ($rows as $tx) {
            $key = $tx['category_name'] ?? 'ບໍ່ລະບຸໝວດ';
            if (!isset($byCat[$key])) {
                $byCat[$key] = [
                    'name'  => $key,
                    'color' => $tx['category_color'] ?? '#EF4444',
                    'icon'  => $tx['category_icon']  ?? 'tag',
                    'total' => 0.0,
                    'count' => 0,
                ];
            }
            $byCat[$key]['total'] += (float) $tx['amount'];
            $byCat[$key]['count']++;
        }
        usort($byCat, fn($a, $b) => $b['total'] <=> $a['total']);

        // Daily trend
        $daily = [];
        foreach ($rows as $tx) {
            $d = $tx['date'];
            $daily[$d] = ($daily[$d] ?? 0.0) + (float) $tx['amount'];
        }
        ksort($daily);

        return [
            'rows'   => $rows,
            'by_cat' => array_values($byCat),
            'daily'  => $daily,
            'totals' => self::totalsFromRows($rows),
        ];
    }

    // ── 6. Cash Flow Report ───────────────────────────────────────────

    /**
     * Rolling monthly cash flow for the last N months.
     * Returns structured monthly data with running balance.
     */
    public function getCashFlow(int $months = 12): array
    {
        // Build date range covering N complete calendar months back from today
        $endMonth   = date('Y-m');
        $startMonth = date('Y-m', strtotime("first day of -{$months} months"));
        [$start]    = $this->monthRange($startMonth);
        [, $end]    = $this->monthRange($endMonth);

        $rows = $this->db->fetchAll(
            "SELECT t.date, t.type, t.amount, c.name AS category_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.status = 'approved'
               AND t.date >= :start AND t.date < :end
             ORDER BY t.date ASC",
            [':start' => $start, ':end' => $end]
        );

        // Seed all months in range
        $monthly   = [];
        $cursor    = new DateTimeImmutable($startMonth . '-01');
        $endDt     = new DateTimeImmutable($endMonth . '-01');
        while ($cursor <= $endDt) {
            $mk = $cursor->format('Y-m');
            $monthly[$mk] = [
                'month'   => $mk,
                'label'   => laoMonthFull((int) $cursor->format('n')) . ' ' . $cursor->format('Y'),
                'income'  => 0.0,
                'expense' => 0.0,
                'net'     => 0.0,
            ];
            $cursor = $cursor->modify('+1 month');
        }

        foreach ($rows as $tx) {
            $mk = substr($tx['date'], 0, 7);
            if (!isset($monthly[$mk])) continue;
            if ($tx['type'] === 'income') $monthly[$mk]['income']  += (float) $tx['amount'];
            else                          $monthly[$mk]['expense'] += (float) $tx['amount'];
        }

        // Compute net and running balance
        $running = 0.0;
        $result  = [];
        foreach ($monthly as &$m) {
            $m['net']     = $m['income'] - $m['expense'];
            $running     += $m['net'];
            $m['running'] = $running;
            $result[]     = $m;
        }
        unset($m);

        return [
            'monthly' => $result,
            'totals'  => self::totalsFromRows($rows),
        ];
    }

    // ── 7. Budget Report ──────────────────────────────────────────────

    /**
     * Budget vs actual for a month.
     * Totals are computed from the joined transaction rows — no secondary query.
     */
    public function getBudget(string $month): array
    {
        [$start, $end] = $this->monthRange($month);

        $rows = $this->db->fetchAll(
            "SELECT b.*,
                    c.name    AS category_name,
                    c.color   AS category_color,
                    c.icon    AS category_icon,
                    COALESCE(SUM(t.amount), 0) AS spent
             FROM budgets b
             LEFT JOIN categories c ON b.category_id = c.id
             LEFT JOIN transactions t
                ON t.category_id = b.category_id
               AND t.type        = 'expense'
               AND t.status      = 'approved'
               AND t.date       >= :start AND t.date < :end
             WHERE b.month = :month
             GROUP BY b.id
             ORDER BY (spent / NULLIF(b.amount, 0)) DESC",
            [':start' => $start, ':end' => $end, ':month' => $month]
        );

        // PHP-computed totals from the result set
        $totalBudget  = array_sum(array_column($rows, 'amount'));
        $totalSpent   = array_sum(array_column($rows, 'spent'));
        $totalRemain  = $totalBudget - $totalSpent;
        $overBudget   = array_filter($rows, fn($r) => (float) $r['spent'] > (float) $r['amount']);

        return [
            'rows'          => $rows,
            'total_budget'  => $totalBudget,
            'total_spent'   => $totalSpent,
            'total_remain'  => $totalRemain,
            'over_budget'   => array_values($overBudget),
            'utilisation'   => $totalBudget > 0
                ? round($totalSpent / $totalBudget * 100, 1)
                : 0.0,
        ];
    }

    // ── 8. Temple Summary Dashboard ───────────────────────────────────

    /**
     * Snapshot of current financial health.
     * All figures derived from approved transactions only.
     */
    public function getTempleSummary(): array
    {
        $currentMonth = date('Y-m');
        $currentYear  = (int) date('Y');
        $lastMonth    = date('Y-m', strtotime('first day of last month'));

        [$mStart, $mEnd]   = $this->monthRange($currentMonth);
        [$lmStart, $lmEnd] = $this->monthRange($lastMonth);
        [$yStart, $yEnd]   = $this->yearRange($currentYear);

        // Three aggregate queries — each returns a single row
        $thisMonth = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS expense,
                COUNT(*) AS tx_count
             FROM transactions
             WHERE status='approved' AND date >= :s AND date < :e",
            [':s' => $mStart, ':e' => $mEnd]
        );

        $prevMonth = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS expense
             FROM transactions
             WHERE status='approved' AND date >= :s AND date < :e",
            [':s' => $lmStart, ':e' => $lmEnd]
        );

        $ytd = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS expense,
                COUNT(*) AS tx_count
             FROM transactions
             WHERE status='approved' AND date >= :s AND date < :e",
            [':s' => $yStart, ':e' => $yEnd]
        );

        // Pending count
        $pending = $this->db->fetch(
            "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS amount
             FROM transactions WHERE status = 'pending'"
        );

        // Top expense categories this month (from same range — no extra round-trip)
        $topExp = $this->db->fetchAll(
            "SELECT c.name AS category, c.color,
                    SUM(t.amount) AS total
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.status='approved' AND t.type='expense'
               AND t.date >= :s AND t.date < :e
             GROUP BY t.category_id
             ORDER BY total DESC
             LIMIT 5",
            [':s' => $mStart, ':e' => $mEnd]
        );

        // Top donors this month
        $topDonors = $this->db->fetchAll(
            "SELECT d.name AS donor, SUM(t.amount) AS total
             FROM transactions t
             LEFT JOIN donors d ON t.donor_id = d.id
             WHERE t.status='approved' AND t.type='income'
               AND t.donor_id IS NOT NULL
               AND t.date >= :s AND t.date < :e
             GROUP BY t.donor_id
             ORDER BY total DESC
             LIMIT 5",
            [':s' => $mStart, ':e' => $mEnd]
        );

        $tmIncome  = (float) ($thisMonth['income']  ?? 0);
        $tmExpense = (float) ($thisMonth['expense'] ?? 0);
        $pmIncome  = (float) ($prevMonth['income']  ?? 0);
        $pmExpense = (float) ($prevMonth['expense'] ?? 0);

        return [
            'current_month' => $currentMonth,
            'this_month'    => [
                'income'   => $tmIncome,
                'expense'  => $tmExpense,
                'net'      => $tmIncome - $tmExpense,
                'tx_count' => (int) ($thisMonth['tx_count'] ?? 0),
            ],
            'prev_month' => [
                'income'  => $pmIncome,
                'expense' => $pmExpense,
                'net'     => $pmIncome - $pmExpense,
            ],
            'income_change'  => $pmIncome  > 0 ? round(($tmIncome  - $pmIncome)  / $pmIncome  * 100, 1) : null,
            'expense_change' => $pmExpense > 0 ? round(($tmExpense - $pmExpense) / $pmExpense * 100, 1) : null,
            'ytd' => [
                'income'   => (float) ($ytd['income']   ?? 0),
                'expense'  => (float) ($ytd['expense']  ?? 0),
                'net'      => (float) ($ytd['income'] ?? 0) - (float) ($ytd['expense'] ?? 0),
                'tx_count' => (int)   ($ytd['tx_count'] ?? 0),
            ],
            'pending'    => [
                'count'  => (int)   ($pending['cnt']    ?? 0),
                'amount' => (float) ($pending['amount'] ?? 0),
            ],
            'top_expenses' => $topExp,
            'top_donors'   => $topDonors,
        ];
    }
}
