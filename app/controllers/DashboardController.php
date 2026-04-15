<?php
class DashboardController extends Controller
{
    private Transaction $transaction;
    private Budget $budget;

    public function __construct()
    {
        $this->requireAuth();
        $this->transaction = new Transaction();
        $this->budget      = new Budget();
    }

    public function index(): void
    {
        $currentMonth = date('Y-m');

        $totalIncome   = $this->transaction->getTotalIncome($currentMonth);
        $totalExpenses = $this->transaction->getTotalExpenses($currentMonth);
        $balance       = $totalIncome - $totalExpenses;
        $savingsRate   = $totalIncome > 0
            ? round((($totalIncome - $totalExpenses) / $totalIncome) * 100, 1)
            : 0;

        $recentTransactions = $this->transaction->getRecent(6);
        $monthlyTotals      = $this->transaction->getMonthlyTotals(6);
        $expensesByCategory = $this->transaction->getExpensesByCategory($currentMonth);
        $budgets            = $this->budget->getAll($currentMonth);

        $this->view('dashboard/index', [
            'balance'            => $balance,
            'totalIncome'        => $totalIncome,
            'totalExpenses'      => $totalExpenses,
            'savingsRate'        => $savingsRate,
            'recentTransactions' => $recentTransactions,
            'monthlyTotals'      => $monthlyTotals,
            'expensesByCategory' => $expensesByCategory,
            'budgets'            => $budgets,
            'currentMonth'       => $currentMonth,
        ], 'Dashboard — ' . APP_NAME, 'dashboard');
    }
}
