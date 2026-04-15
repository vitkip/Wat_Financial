<?php
class TransactionsController extends Controller
{
    private Transaction $transaction;
    private Category    $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->transaction = new Transaction();
        $this->category    = new Category();
    }

    public function index(): void
    {
        // ── ດຶງ filter ຈາກ GET params ────────────────────────────
        $filters = [
            'q'           => trim($_GET['q']          ?? ''),
            'type'        => $_GET['type']             ?? '',
            'category_id' => $_GET['category_id']      ?? '',
            'date_from'   => $_GET['date_from']        ?? '',
            'date_to'     => $_GET['date_to']          ?? '',
            'amount_min'  => $_GET['amount_min']       ?? '',
            'amount_max'  => $_GET['amount_max']       ?? '',
        ];

        // ຖ້າ active filter ໃດໜຶ່ງ → ໃຊ້ search(), ຖ້າບໍ່ → getAll()
        $hasFilter = array_filter(array_values($filters), fn($v) => $v !== '');

        $total      = $hasFilter
            ? $this->transaction->countFiltered($filters)
            : $this->transaction->count();

        $totalPages = max(1, (int) ceil($total / ITEMS_PER_PAGE));
        $page       = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
        $offset     = ($page - 1) * ITEMS_PER_PAGE;

        $transactions = $hasFilter
            ? $this->transaction->search($filters, ITEMS_PER_PAGE, $offset)
            : $this->transaction->getAll(ITEMS_PER_PAGE, $offset);

        // ຄຳນວນ filter summary (income/expense totals ຂອງຜົນຄົ້ນ)
        $filterIncome  = 0.0;
        $filterExpense = 0.0;
        foreach ($transactions as $tx) {
            if ($tx['type'] === 'income')  $filterIncome  += (float)$tx['amount'];
            else                           $filterExpense += (float)$tx['amount'];
        }

        $this->view('transactions/index', [
            'transactions'  => $transactions,
            'categories'    => $this->category->getAll(),
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'filters'       => $filters,
            'hasFilter'     => (bool)$hasFilter,
            'filterIncome'  => $filterIncome,
            'filterExpense' => $filterExpense,
        ], 'ລາຍການ — ' . APP_NAME, 'transactions');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
        }
        $this->verifyCsrf();

        $data = [
            'type'        => $this->sanitizeType($_POST['type'] ?? ''),
            'amount'      => (float)($_POST['amount'] ?? 0),
            'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'date'        => $_POST['date']  ?? date('Y-m-d'),
            'notes'       => htmlspecialchars(trim($_POST['notes'] ?? '')),
        ];

        if ($data['amount'] <= 0 || empty($data['description'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຈຳນວນ ແລະ ລາຍລະອຽດ ໃຫ້ຖືກຕ້ອງ.');
            $this->redirect('transactions');
        }

        $this->transaction->create($data);
        $this->flash('success', 'ເພີ່ມລາຍການສຳເລັດ.');
        $this->redirect('transactions');
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
        }
        $this->verifyCsrf();

        $data = [
            'type'        => $this->sanitizeType($_POST['type'] ?? ''),
            'amount'      => (float)($_POST['amount'] ?? 0),
            'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'date'        => $_POST['date']  ?? date('Y-m-d'),
            'notes'       => htmlspecialchars(trim($_POST['notes'] ?? '')),
        ];

        if ($data['amount'] <= 0 || empty($data['description'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຈຳນວນ ແລະ ລາຍລະອຽດ ໃຫ້ຖືກຕ້ອງ.');
            $this->redirect('transactions');
        }

        $this->transaction->update($id, $data);
        $this->flash('success', 'ອັບເດດລາຍການສຳເລັດ.');
        $this->redirect('transactions');
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
        }
        $this->verifyCsrf();

        $this->transaction->delete($id);
        $this->flash('success', 'ລຶບລາຍການສຳເລັດ.');
        $this->redirect('transactions');
    }
}
