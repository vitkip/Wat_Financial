<?php
/**
 * ReportsController
 *
 * Routes:
 *   GET /reports              → hub / annual summary
 *   GET /reports/daily        → daily report  (?date=YYYY-MM-DD)
 *   GET /reports/monthly      → monthly report (?month=YYYY-MM)
 *   GET /reports/donations    → donation report (?from=&to=)
 *   GET /reports/expenses     → expense report (?from=&to=)
 *   GET /reports/cashflow     → cash flow (?months=12)
 *   GET /reports/budget       → budget vs actual (?month=YYYY-MM)
 *   GET /reports/summary      → temple summary dashboard
 */
class ReportsController extends Controller
{
    private Report      $report;
    private Transaction $transaction;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('reports.view', '');
        $this->report      = new Report();
        $this->transaction = new Transaction();
    }

    // ── Hub / Annual ──────────────────────────────────────────────────

    public function index(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $data = $this->report->getAnnual($year);

        $this->view('reports/index', [
            'year'        => $year,
            'monthly'     => $data['monthly'],
            'exp_by_cat'  => $data['exp_by_cat'],
            'totals'      => $data['totals'],
            'canExport'   => $this->can('reports.export'),
        ], "ລາຍງານປີ {$year} — " . APP_NAME, 'reports');
    }

    // ── Daily ─────────────────────────────────────────────────────────

    public function daily(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        // Sanitise: must be a valid date, not in the future
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > date('Y-m-d')) {
            $date = date('Y-m-d');
        }

        $rows   = $this->report->getDaily($date);
        $totals = Report::totalsFromRows($rows);

        $this->view('reports/daily', [
            'date'      => $date,
            'rows'      => $rows,
            'totals'    => $totals,
            'canExport' => $this->can('reports.export'),
        ], "ລາຍງານປະຈຳວັນ — " . APP_NAME, 'reports');
    }

    // ── Monthly ───────────────────────────────────────────────────────

    public function monthly(): void
    {
        $month = $_GET['month'] ?? date('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = date('Y-m');
        }

        $data = $this->report->getMonthly($month);
        [$y, $m] = explode('-', $month);

        $this->view('reports/monthly', [
            'month'     => $month,
            'label'     => laoMonthFull((int) $m) . ' ' . $y,
            'rows'      => $data['rows'],
            'daily'     => $data['daily'],
            'by_cat'    => $data['by_cat'],
            'totals'    => $data['totals'],
            'canExport' => $this->can('reports.export'),
        ], "ລາຍງານລາຍເດືອນ — " . APP_NAME, 'reports');
    }

    // ── Donations ─────────────────────────────────────────────────────

    public function donations(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');
        if ($from > $to) [$from, $to] = [$to, $from];

        $data = $this->report->getDonations($from, $to);

        $this->view('reports/donations', [
            'from'      => $from,
            'to'        => $to,
            'rows'      => $data['rows'],
            'by_donor'  => $data['by_donor'],
            'by_cat'    => $data['by_cat'],
            'totals'    => $data['totals'],
            'canExport' => $this->can('reports.export'),
        ], "ລາຍງານການທານ — " . APP_NAME, 'reports');
    }

    // ── Expenses ──────────────────────────────────────────────────────

    public function expenses(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');
        if ($from > $to) [$from, $to] = [$to, $from];

        $data = $this->report->getExpenses($from, $to);

        $this->view('reports/expenses', [
            'from'      => $from,
            'to'        => $to,
            'rows'      => $data['rows'],
            'by_cat'    => $data['by_cat'],
            'daily'     => $data['daily'],
            'totals'    => $data['totals'],
            'canExport' => $this->can('reports.export'),
        ], "ລາຍງານລາຍຈ່າຍ — " . APP_NAME, 'reports');
    }

    // ── Cash Flow ─────────────────────────────────────────────────────

    public function cashflow(): void
    {
        $months = max(3, min(24, (int) ($_GET['months'] ?? 12)));
        $data   = $this->report->getCashFlow($months);

        $this->view('reports/cashflow', [
            'months'    => $months,
            'monthly'   => $data['monthly'],
            'totals'    => $data['totals'],
            'canExport' => $this->can('reports.export'),
        ], "Cash Flow — " . APP_NAME, 'reports');
    }

    // ── Budget vs Actual ──────────────────────────────────────────────

    public function budget(): void
    {
        $month = $_GET['month'] ?? date('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = date('Y-m');
        }

        $data = $this->report->getBudget($month);
        [$y, $m] = explode('-', $month);

        $this->view('reports/budget', [
            'month'         => $month,
            'label'         => laoMonthFull((int) $m) . ' ' . $y,
            'rows'          => $data['rows'],
            'total_budget'  => $data['total_budget'],
            'total_spent'   => $data['total_spent'],
            'total_remain'  => $data['total_remain'],
            'over_budget'   => $data['over_budget'],
            'utilisation'   => $data['utilisation'],
            'canExport'     => $this->can('reports.export'),
        ], "ງົບປະມານ vs ຕົວຈິງ — " . APP_NAME, 'reports');
    }

    // ── Temple Summary ────────────────────────────────────────────────

    public function summary(): void
    {
        $data = Cache::remember(
            'temple_summary_' . date('Y-m-d-H'),  // cache for 1 hour (keyed by hour)
            3600,
            fn() => $this->report->getTempleSummary()
        );

        $this->view('reports/summary', [
            'summary'   => $data,
            'canExport' => $this->can('reports.export'),
        ], "ສະຫຼຸບວັດ — " . APP_NAME, 'reports');
    }
}
