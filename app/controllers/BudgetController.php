<?php
class BudgetController extends Controller
{
    private Budget   $budget;
    private Category $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('budgets.view', '');
        $this->budget   = new Budget();
        $this->category = new Category();
    }

    public function index(): void
    {
        $month      = $_GET['month'] ?? date('Y-m');
        $budgets    = $this->budget->getAll($month);
        $categories = $this->category->getAll();
        // Derive totals from the already-fetched $budgets — no extra DB query
        $totalBudgeted = array_sum(array_column($budgets, 'amount'));
        $totalSpent    = array_sum(array_column($budgets, 'spent'));

        $this->view('budget/index', [
            'budgets'       => $budgets,
            'categories'    => $categories,
            'month'         => $month,
            'totalBudgeted' => $totalBudgeted,
            'totalSpent'    => $totalSpent,
        ], 'ງົບປະມານ — ' . APP_NAME, 'budget');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('budget');
        }
        $this->verifyCsrf();
        $this->requirePermission('budgets.create', 'budget');

        $rawMonth = $_POST['month'] ?? date('Y-m');
        $month    = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $rawMonth) ? $rawMonth : date('Y-m');

        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'amount'      => (float)($_POST['amount'] ?? 0),
            'month'       => $month,
        ];

        if ($data['category_id'] <= 0 || $data['amount'] <= 0) {
            $this->flash('error', 'ກະລຸນາເລືອກໝວດໝູ່ ແລະ ໃສ່ຈຳນວນ.');
            $this->redirect('budget?month=' . $data['month']);
        }

        $this->budget->create($data);
        $this->flash('success', 'ຕັ້ງງົບປະມານສຳເລັດ.');
        $this->redirect('budget?month=' . $data['month']);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('budget');
        }
        $this->verifyCsrf();
        $this->requirePermission('budgets.delete', 'budget');

        $this->budget->delete($id);
        $this->flash('success', 'ລຶບງົບປະມານສຳເລັດ.');
        $this->redirect('budget');
    }
}
