<?php
class ReportsController extends Controller
{
    private Transaction $transaction;

    public function __construct()
    {
        $this->requireAuth();
        $this->transaction = new Transaction();
    }

    public function index(): void
    {
        $year   = (int)($_GET['year'] ?? date('Y'));
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthKey = sprintf('%04d-%02d', $year, $m);
            $income   = $this->transaction->getTotalIncome($monthKey);
            $expenses = $this->transaction->getTotalExpenses($monthKey);
            $months[] = [
                'month'    => $monthKey,
                'label'    => laoMonthFull($m),
                'income'   => $income,
                'expenses' => $expenses,
                'net'      => $income - $expenses,
            ];
        }

        $yearlyIncome   = array_sum(array_column($months, 'income'));
        $yearlyExpenses = array_sum(array_column($months, 'expenses'));
        $yearlyNet      = $yearlyIncome - $yearlyExpenses;

        $topCategories = $this->transaction->getExpensesByCategory(null, $year);

        $this->view('reports/index', [
            'months'         => $months,
            'year'           => $year,
            'yearlyIncome'   => $yearlyIncome,
            'yearlyExpenses' => $yearlyExpenses,
            'yearlyNet'      => $yearlyNet,
            'topCategories'  => $topCategories,
        ], 'Reports — ' . APP_NAME, 'reports');
    }
}
