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

        // ດຶງທຸລະກຳທັງໝົດທີ່ match filter (ບໍ່ pagination)
        $hasFilter = array_filter(array_values($filters), fn($v) => $v !== '');
        $rows = $hasFilter
            ? $this->transaction->searchAll($filters)
            : $this->transaction->getAllForExport();

        if ($format === 'pdf') {
            $this->renderTransactionsPdf($rows, $filters);
        } else {
            $this->streamTransactionsCsv($rows, $filters);
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
            $title = 'ລາຍງານປີ ' . $year;
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

    private function streamTransactionsCsv(array $rows, array $filters): void
    {
        $filename = 'transactions_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM ສຳລັບ Excel ໃຫ້ຮຮ Lao ຖືກຕ້ອງ
        fputs($out, "\xEF\xBB\xBF");

        // Header row
        fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', 'ຈຳນວນ (' . CURRENCY_CODE . ')', 'ໝາຍເຫດ']);

        $i = 1;
        foreach ($rows as $tx) {
            fputcsv($out, [
                $i++,
                $tx['date'],
                $tx['type'] === 'income' ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ',
                $tx['description'],
                $tx['category_name'] ?? '—',
                number_format((float)$tx['amount'], defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0, '.', ''),
                $tx['notes'] ?? '',
            ]);
        }

        // Summary rows
        $income  = array_sum(array_map(fn($r) => $r['type'] === 'income'  ? (float)$r['amount'] : 0, $rows));
        $expense = array_sum(array_map(fn($r) => $r['type'] === 'expense' ? (float)$r['amount'] : 0, $rows));
        fputcsv($out, []);
        fputcsv($out, ['', '', '', '', 'ລວມລາຍຮັບ',  number_format($income,  defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0, '.', ''), '']);
        fputcsv($out, ['', '', '', '', 'ລວມລາຍຈ່າຍ', number_format($expense, defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0, '.', ''), '']);
        fputcsv($out, ['', '', '', '', 'ຍອດສຸດທິ',   number_format($income - $expense, defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0, '.', ''), '']);

        fclose($out);
        exit;
    }

    private function streamReportCsv(array $rows, array $monthlySummary, string $title, int $year, string $month): void
    {
        $slug     = $month !== '' ? "{$year}_{$month}" : (string)$year;
        $filename = 'report_' . $slug . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
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
            fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', 'ຈຳນວນ (' . CURRENCY_CODE . ')', 'ໝາຍເຫດ']);

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
            fputcsv($out, ['ລາຍງານລາຍປີ ' . $year]);
            fputcsv($out, []);
            fputcsv($out, ['ເດືອນ', 'ລາຍຮັບ (' . CURRENCY_CODE . ')', 'ລາຍຈ່າຍ (' . CURRENCY_CODE . ')', 'ຍອດສຸດທິ (' . CURRENCY_CODE . ')']);

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
            fputcsv($out, ['#', 'ວັນທີ', 'ປະເພດ', 'ລາຍລະອຽດ', 'ໝວດໝູ່', 'ຈຳນວນ (' . CURRENCY_CODE . ')', 'ໝາຍເຫດ']);

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
        ];
    }
}
