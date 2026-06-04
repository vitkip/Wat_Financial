<?php
class DashboardController extends Controller
{
    private Transaction $transaction;
    private Budget      $budget;
    private Account     $account;

    public function __construct()
    {
        $this->requireAuth();
        $this->transaction = new Transaction();
        $this->budget      = new Budget();
        $this->account     = new Account();
    }

    public function index(): void
    {
        $currentMonth = date('Y-m');

        // Asset balance from cached account balances (no extra aggregation query)
        $accounts     = $this->account->getActive();
        $totalBalance = 0.0;
        foreach ($accounts as $acc) {
            if ($acc['type'] === 'asset') {
                $totalBalance += (float) $acc['balance'];
            }
        }

        // Single query replaces the previous getTotalIncome() + getTotalExpenses() pair
        $summary       = $this->transaction->getMonthSummary($currentMonth);
        $totalIncome   = $summary['income'];
        $totalExpenses = $summary['expenses'];
        $savingsRate   = $totalIncome > 0
            ? round((($totalIncome - $totalExpenses) / $totalIncome) * 100, 1)
            : 0;

        // Monthly chart — cached for 3 minutes to survive rapid dashboard reloads
        $monthlyTotals = Cache::remember(
            'dashboard_monthly_' . $currentMonth,
            180,
            fn() => $this->transaction->getMonthlyTotals(6)
        );

        $pendingTransactions = $this->transaction->getPending(5);
        $recentTransactions  = $this->transaction->getRecent(6);
        $expensesByCategory  = $this->transaction->getExpensesByCategory($currentMonth);

        // getAll() already includes b.amount — derive total in PHP, no extra query
        $budgets       = $this->budget->getAll($currentMonth);
        $totalBudgeted = array_sum(array_column($budgets, 'amount'));
        $totalSpent    = array_sum(array_column($budgets, 'spent'));

        $this->view('dashboard/index', [
            'balance'             => $totalBalance,
            'accounts'            => $accounts,
            'totalIncome'         => $totalIncome,
            'totalExpenses'       => $totalExpenses,
            'savingsRate'         => $savingsRate,
            'recentTransactions'  => $recentTransactions,
            'pendingTransactions' => $pendingTransactions,
            'monthlyTotals'       => $monthlyTotals,
            'expensesByCategory'  => $expensesByCategory,
            'budgets'             => $budgets,
            'totalBudgeted'       => $totalBudgeted,
            'totalSpent'          => $totalSpent,
            'currentMonth'        => $currentMonth,
            'user_role'           => $_SESSION['user_role'] ?? 'clerk',
        ], 'Dashboard — ' . APP_NAME, 'dashboard');
    }
}
