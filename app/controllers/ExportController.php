<?php
/**
 * ExportController — PDF ແລະ CSV/Excel export
 *
 * Routes:
 *   GET /export/transactions?format=csv|pdf  [+ same filter params as transactions page]
 *   GET /export/report?format=csv|pdf&year=YYYY[&month=MM]
 */
class ExportController extends Controller
{
    private Transaction $transaction;
    private Category    $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('reports.export', '');
        $this->transaction = new Transaction();
        $this->category    = new Category();
    }

    // ──────────────────────────────────────────────────────────────
    // Transactions Export
    // ──────────────────────────────────────────────────────────────

    public function transactions(): void
    {
        $format  = strtolower($_GET['format'] ?? 'csv');
        $filters = $this->parseFilters();

        if ($format === 'pdf') {
            // PDF needs the full array in memory for the view renderer
            $rows = $this->transaction->searchAll($filters);
            $this->renderTransactionsPdf($rows, $filters);
        } else {
            // CSV streams row-by-row via PDO cursor — O(1) memory regardless of dataset size
            $cursor = $this->transaction->cursorForExport($filters);
            $this->streamTransactionsCsvCursor($cursor);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Report Export (monthly or yearly)
    // ──────────────────────────────────────────────────────────────

    public function report(): void
    {
        $format = strtolower($_GET['format'] ?? 'csv');
        $year   = (int)($_GET['year']  ?? date('Y'));
        $month  = trim($_GET['month'] ?? '');   // '01'…'12' or ''

        if ($month !== '') {
            $monthKey = sprintf('%04d-%02d', $year, (int)$month);
            $rows     = $this->transaction->getByMonth($monthKey);
            $title    = laoMonthFull((int)$month) . ' ' . $year;
        } else {
            $rows  = $this->transaction->getByYear($year);
            $title = "ລາຍງານປີ {$year}";
        }

        // ສ້າງ monthly summary rows ສຳລັບ yearly report
        $monthlySummary = [];
        if ($month === '') {
            for ($m = 1; $m <= 12; $m++) {
                $mk = sprintf('%04d-%02d', $year, $m);
                $monthlySummary[$mk] = [
                    'label'    => laoMonthFull($m),
                    'income'   => 0.0,
                    'expenses' => 0.0,
                ];
            }
            foreach ($rows as $tx) {
                $mk = substr($tx['date'], 0, 7);
                if (isset($monthlySummary[$mk])) {
                    if ($tx['type'] === 'income') {
                        $monthlySummary[$mk]['income'] += (float)$tx['amount'];
                    } else {
                        $monthlySummary[$mk]['expenses'] += (float)$tx['amount'];
                    }
                }
            }
        }

        if ($format === 'pdf') {
            $this->renderReportPdf($rows, $monthlySummary, $title, $year, $month);
        } else {
            $this->streamReportCsv($rows, $monthlySummary, $title, $year, $month);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // CSV Streams
    // ──────────────────────────────────────────────────────────────

    /**
     * Streams CSV row-by-row from a PDO cursor.
     * Memory usage stays constant regardless of how many rows are exported.
     */
    private function streamTransactionsCsvCursor(PDOStatement $cursor): void
    {
        $filename = 'transactions_' . date('Ymd_His') . '.csv';
        $dp       = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // BOM for Excel/Lao support

        $cc = CURRENCY_CODE;
        fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', "ຈຳນວນ ({$cc})", 'ໝາຍເຫດ']);

        $i = 1;
        $income  = 0.0;
        $expense = 0.0;

        while ($tx = $cursor->fetch()) {
            $amount = (float) $tx['amount'];
            if ($tx['type'] === 'income')  $income  += $amount;
            else                           $expense += $amount;

            fputcsv($out, [
                $i++,
                $tx['date'],
                $tx['type'] === 'income' ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ',
                $tx['description'],
                $tx['category_name'] ?? '—',
                number_format($amount, $dp, '.', ''),
                $tx['notes'] ?? '',
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['', '', '', '', 'ລວມລາຍຮັບ',  number_format($income,           $dp, '.', ''), '']);
        fputcsv($out, ['', '', '', '', 'ລວມລາຍຈ່າຍ', number_format($expense,          $dp, '.', ''), '']);
        fputcsv($out, ['', '', '', '', 'ຍອດສຸດທິ',   number_format($income - $expense, $dp, '.', ''), '']);

        fclose($out);
        exit;
    }

    private function streamReportCsv(array $rows, array $monthlySummary, string $title, int $year, string $month): void
    {
        $slug     = $month !== '' ? "{$year}_{$month}" : (string) $year;
        $today    = date('Ymd');
        $filename = "report_{$slug}_{$today}.csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        $dp = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;

        if ($month !== '' || empty($monthlySummary)) {
            // Monthly detail
            fputcsv($out, [$title]);
            fputcsv($out, []);
            $cc = CURRENCY_CODE;
        fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', "ຈຳນວນ ({$cc})", 'ໝາຍເຫດ']);

            $i = 1;
            foreach ($rows as $tx) {
                fputcsv($out, [
                    $i++,
                    $tx['date'],
                    $tx['type'] === 'income' ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ',
                    $tx['description'],
                    $tx['category_name'] ?? '—',
                    number_format((float)$tx['amount'], $dp, '.', ''),
                    $tx['notes'] ?? '',
                ]);
            }
        } else {
            // Yearly summary sheet
            $cc = CURRENCY_CODE;
            fputcsv($out, ["ລາຍງານລາຍປີ {$year}"]);
            fputcsv($out, []);
            fputcsv($out, ['ເດືອນ', "ລາຍຮັບ ({$cc})", "ລາຍຈ່າຍ ({$cc})", "ຍອດສຸດທິ ({$cc})"]);

            foreach ($monthlySummary as $row) {
                fputcsv($out, [
                    $row['label'],
                    number_format($row['income'],   $dp, '.', ''),
                    number_format($row['expenses'], $dp, '.', ''),
                    number_format($row['income'] - $row['expenses'], $dp, '.', ''),
                ]);
            }

            // Yearly total
            $yi = array_sum(array_column($monthlySummary, 'income'));
            $ye = array_sum(array_column($monthlySummary, 'expenses'));
            fputcsv($out, []);
            fputcsv($out, ['ລວມທັງປີ', number_format($yi, $dp, '.', ''), number_format($ye, $dp, '.', ''), number_format($yi - $ye, $dp, '.', '')]);

            // Detail sheet separator
            fputcsv($out, []);
            fputcsv($out, ['── ລາຍການທຸລະກຳທັງໝົດ ──']);
            $cc = CURRENCY_CODE;
        fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', "ຈຳນວນ ({$cc})", 'ໝາຍເຫດ']);

            $i = 1;
            foreach ($rows as $tx) {
                fputcsv($out, [
                    $i++,
                    $tx['date'],
                    $tx['type'] === 'income' ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ',
                    $tx['description'],
                    $tx['category_name'] ?? '—',
                    number_format((float)$tx['amount'], $dp, '.', ''),
                    $tx['notes'] ?? '',
                ]);
            }
        }

        fclose($out);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // PDF (HTML print view)
    // ──────────────────────────────────────────────────────────────

    private function renderTransactionsPdf(array $rows, array $filters): void
    {
        $income  = array_sum(array_map(fn($r) => $r['type'] === 'income'  ? (float)$r['amount'] : 0, $rows));
        $expense = array_sum(array_map(fn($r) => $r['type'] === 'expense' ? (float)$r['amount'] : 0, $rows));

        $this->view('export/transactions-pdf', [
            'rows'    => $rows,
            'filters' => $filters,
            'income'  => $income,
            'expense' => $expense,
            'net'     => $income - $expense,
        ], 'Export Transactions', 'transactions', 'export');
    }

    private function renderReportPdf(array $rows, array $monthlySummary, string $title, int $year, string $month): void
    {
        $income  = array_sum(array_map(fn($r) => $r['type'] === 'income'  ? (float)$r['amount'] : 0, $rows));
        $expense = array_sum(array_map(fn($r) => $r['type'] === 'expense' ? (float)$r['amount'] : 0, $rows));

        $this->view('export/report-pdf', [
            'rows'           => $rows,
            'monthlySummary' => $monthlySummary,
            'title'          => $title,
            'year'           => $year,
            'month'          => $month,
            'income'         => $income,
            'expense'        => $expense,
            'net'            => $income - $expense,
        ], 'Export Report', 'reports', 'export');
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    private function parseFilters(): array
    {
        return [
            'q'           => trim($_GET['q']           ?? ''),
            'type'        => $_GET['type']              ?? '',
            'category_id' => $_GET['category_id']       ?? '',
            'date_from'   => $_GET['date_from']         ?? '',
            'date_to'     => $_GET['date_to']           ?? '',
            'amount_min'  => $_GET['amount_min']        ?? '',
            'amount_max'  => $_GET['amount_max']        ?? '',
            'status'      => $_GET['status']            ?? 'approved',
        ];
    }
}
