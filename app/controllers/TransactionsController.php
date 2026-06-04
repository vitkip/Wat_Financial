<?php
class TransactionsController extends Controller
{
    private Transaction $transaction;
    private Category    $category;
    private Account     $account;
    private Donor       $donor;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAnyPermission([
            'transactions.view_own', 'transactions.view_all', 'transactions.create',
        ], '');
        $this->transaction = new Transaction();
        $this->category    = new Category();
        $this->account     = new Account();
        $this->donor       = new Donor();
    }

    public function index(): void
    {
        $filters = [
            'q'           => trim($_GET['q']          ?? ''),
            'type'        => $_GET['type']             ?? '',
            'status'      => $_GET['status']           ?? '',
            'category_id' => $_GET['category_id']      ?? '',
            'date_from'   => $_GET['date_from']        ?? '',
            'date_to'     => $_GET['date_to']          ?? '',
            'amount_min'  => $_GET['amount_min']       ?? '',
            'amount_max'  => $_GET['amount_max']       ?? '',
        ];

        $hasFilter = (bool) array_filter(array_values($filters), fn($v) => $v !== '');

        // searchWithCount() returns rows + total in a single window-function query,
        // replacing the previous countFiltered() + search() double round-trip.
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        if ($hasFilter) {
            $result = $this->transaction->searchWithCount($filters, ITEMS_PER_PAGE, $offset);
        } else {
            $result = $this->transaction->searchWithCount([], ITEMS_PER_PAGE, $offset);
        }

        $transactions = $result['rows'];
        $total        = $result['total'];
        $totalPages   = max(1, (int) ceil($total / ITEMS_PER_PAGE));
        $page         = min($page, $totalPages);

        // Compute page-level income/expense totals for the stat chips in the header
        $filterIncome  = 0.0;
        $filterExpense = 0.0;
        foreach ($transactions as $tx) {
            if ($tx['type'] === 'income')  $filterIncome  += (float) $tx['amount'];
            else                           $filterExpense += (float) $tx['amount'];
        }

        $this->view('transactions/index', [
            'transactions'  => $transactions,
            'categories'    => $this->category->getAll(),
            'accounts'      => $this->account->getActive(),
            // Lightweight id+name+phone only — heavy aggregate query deferred to donations page
            'donors'        => $this->donor->getAllForSelect(),
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'filters'       => $filters,
            'hasFilter'     => $hasFilter,
            'filterIncome'  => $filterIncome,
            'filterExpense' => $filterExpense,
            'user_role'     => $_SESSION['user_role'] ?? 'viewer',
        ], 'ລາຍການທຸລະກຳ — ' . APP_NAME, 'transactions');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
        }
        $this->verifyCsrf();

        $amount = (float)($_POST['amount'] ?? 0);
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));

        if ($amount <= 0 || empty($description)) {
            $this->flash('error', 'ກະລຸນາໃສ່ຈຳນວນ ແລະ ລາຍລະອຽດ ໃຫ້ຖືກຕ້ອງ.');
            $this->redirect('transactions');
        }

        $this->requirePermission('transactions.create', 'transactions');

        // Transactions from users with approve permission go straight to approved;
        // others enter the pending queue for review.
        $status = $this->can('transactions.approve') ? 'approved' : 'pending';

        // Validate and sanitise date: must be a real calendar date and not in the future.
        $rawDate = $_POST['date'] ?? date('Y-m-d');
        $date = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)
                 && checkdate((int)substr($rawDate, 5, 2), (int)substr($rawDate, 8, 2), (int)substr($rawDate, 0, 4)))
                ? $rawDate : date('Y-m-d');
        if ($date > date('Y-m-d')) {
            $this->flash('error', 'ບໍ່ສາມາດບັນທຶກທຸລະກຳລ່ວງໜ້າໄດ້.');
            $this->redirect('transactions');
        }

        $data = [
            'type'         => $this->sanitizeType($_POST['type'] ?? ''),
            'amount'       => $amount,
            'description'  => $description,
            'category_id'  => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'account_id'   => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
            'donor_id'     => !empty($_POST['donor_id']) ? (int)$_POST['donor_id'] : null,
            'date'         => $date,
            'notes'        => htmlspecialchars(trim($_POST['notes'] ?? '')),
            'reference_no' => htmlspecialchars(trim($_POST['reference_no'] ?? '')),
            'status'       => $status,
            'created_by'   => $_SESSION['user_id']
        ];
        // Validate category matches transaction type
        if (!empty($data['category_id'])) {
            $cat = $this->category->getById($data['category_id']);
            if ($cat && $cat['type'] !== 'both' && $cat['type'] !== $data['type']) {
                $this->flash('error', 'ໝວດໝູ່ທີ່ເລືອກບໍ່ກົງກັບປະເພດທຸລະກຳ.');
                $this->redirect('transactions');
            }
        }

        try {
            $this->transaction->create($data);
            if ($status === 'pending') {
                $this->flash('success', 'ເພີ່ມລາຍການສຳເລັດ. ລໍຖ້າການອະນຸມັດ.');
            } else {
                $this->flash('success', 'ເພີ່ມລາຍການສຳເລັດ.');
            }
        } catch (Exception $e) {
            $this->flash('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກ: ' . $e->getMessage());
        }

        $this->redirect('transactions');
    }

    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('transactions');
        $this->verifyCsrf();

        $this->requirePermission('transactions.approve', 'transactions');

        if ($this->transaction->approve($id, $_SESSION['user_id'])) {
            $this->flash('success', 'ອະນຸມັດລາຍການສຳເລັດ.');
        } else {
            $this->flash('error', 'ບໍ່ສາມາດອະນຸມັດໄດ້ ຫຼື ອະນຸມັດແລ້ວ.');
        }
        $this->redirect('transactions');
    }

    public function reject(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('transactions');
        $this->verifyCsrf();

        $this->requirePermission('transactions.reject', 'transactions');

        if ($this->transaction->reject($id, $_SESSION['user_id'])) {
            $this->flash('success', 'ປະຕິເສດລາຍການສຳເລັດ.');
        } else {
            $this->flash('error', 'ບໍ່ສາມາດປະຕິເສດໄດ້.');
        }
        $this->redirect('transactions');
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('transactions');
        $this->verifyCsrf();
        $this->requirePermission('transactions.edit_own', 'transactions');

        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        if (empty($description)) {
            $this->flash('error', 'ກະລຸນາໃສ່ລາຍລະອຽດ.');
            $this->redirect('transactions');
        }

        $rawDate = $_POST['date'] ?? date('Y-m-d');
        $date = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)
                 && checkdate((int)substr($rawDate, 5, 2), (int)substr($rawDate, 8, 2), (int)substr($rawDate, 0, 4)))
                ? $rawDate : date('Y-m-d');
        if ($date > date('Y-m-d')) {
            $this->flash('error', 'ບໍ່ສາມາດບັນທຶກທຸລະກຳລ່ວງໜ້າໄດ້.');
            $this->redirect('transactions');
        }

        $data = [
            'description'  => $description,
            'category_id'  => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'date'         => $date,
            'notes'        => htmlspecialchars(trim($_POST['notes'] ?? '')),
            'donor_id'     => !empty($_POST['donor_id']) ? (int)$_POST['donor_id'] : null,
        ];

        try {
            $this->transaction->update($id, $data);
            $this->flash('success', 'ແກ້ໄຂລາຍການສຳເລັດ.');
        } catch (Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        $this->redirect('transactions');
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('transactions');
        $this->verifyCsrf();

        $this->requirePermission('transactions.delete', 'transactions');

        try {
            $this->transaction->delete($id);
            $this->flash('success', 'ລຶບລາຍການສຳເລັດ.');
        } catch (Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('transactions');
    }
}
