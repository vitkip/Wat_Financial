<?php
class RecurringController extends Controller
{
    private RecurringTransaction $recurring;
    private Transaction          $transaction;
    private Category             $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('recurring.view', '');
        $this->recurring    = new RecurringTransaction();
        $this->transaction  = new Transaction();
        $this->category     = new Category();
    }

    public function index(): void
    {
        $this->view('recurring/index', [
            'items'      => $this->recurring->getAll(),
            'categories' => $this->category->getAll(),
        ], 'ລາຍການຊ້ຳ — ' . APP_NAME, 'recurring');
    }

    /** POST: ສ້າງລາຍການຊ້ຳໃໝ່ */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('recurring');
        }
        $this->verifyCsrf();
        $this->requirePermission('recurring.create', 'recurring');

        $data = [
            'type'         => $this->sanitizeType($_POST['type'] ?? ''), // ✅ Sanitize enum
            'amount'       => (float)($_POST['amount'] ?? 0),
            'description'  => htmlspecialchars(trim($_POST['description'] ?? '')),
            'category_id'  => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'frequency'    => $_POST['frequency'] ?? 'monthly',
            'day_of_month' => !empty($_POST['day_of_month']) ? (int)$_POST['day_of_month'] : 1,
            'start_date'   => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date'     => !empty($_POST['end_date']) ? $_POST['end_date'] : null, // ✅ ຮອງຮັບ end_date
            'notes'        => htmlspecialchars(trim($_POST['notes'] ?? '')),
        ];

        // Validate frequency
        if (!in_array($data['frequency'], ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $data['frequency'] = 'monthly';
        }

        // Validate day_of_month
        $data['day_of_month'] = max(1, min(31, $data['day_of_month']));

        // Validate start_date is a real calendar date; fall back to today if malformed.
        $sd = $data['start_date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sd) ||
            !checkdate((int)substr($sd,5,2), (int)substr($sd,8,2), (int)substr($sd,0,4))) {
            $data['start_date'] = date('Y-m-d');
        }

        // Validate optional end_date.
        if (!empty($data['end_date'])) {
            $ed = $data['end_date'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ed) ||
                !checkdate((int)substr($ed,5,2), (int)substr($ed,8,2), (int)substr($ed,0,4))) {
                $data['end_date'] = null; // silently clear an unparseable value
            } elseif ($data['end_date'] <= $data['start_date']) {
                $this->flash('error', 'ວັນໝົດກຳນົດຕ້ອງຢູ່ຫຼັງວັນເລີ່ມຕົ້ນ.');
                $this->redirect('recurring');
            }
        }

        if ($data['amount'] <= 0 || empty($data['description'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຈຳນວນ ແລະ ລາຍລະອຽດ ໃຫ້ຖືກຕ້ອງ.');
            $this->redirect('recurring');
        }

        try {
            $this->recurring->create($data);
            $this->flash('success', 'ເພີ່ມລາຍການຊ້ຳສຳເລັດ.');
        } catch (Exception $e) {
            $this->flash('error', 'ເກີດຂໍ້ຜິດພາດ. ກະລຸນາລອງໃໝ່.');
        }
        $this->redirect('recurring');
    }

    /** POST: ເປີດ/ປິດ ລາຍການຊ້ຳ */
    public function toggle(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('recurring');
        }
        $this->verifyCsrf();

        $this->recurring->toggle($id);
        $this->flash('success', 'ອັບເດດສະຖານະສຳເລັດ.');
        $this->redirect('recurring');
    }

    /** POST: ສ້າງ transaction ຈາກລາຍການຊ້ຳ */
    public function generate(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('recurring');
        }
        $this->verifyCsrf();

        $rec = $this->recurring->getById($id);
        if (!$rec) {
            $this->flash('error', 'ບໍ່ພົບລາຍການຊ້ຳ.');
            $this->redirect('recurring');
        }

        if (!(bool)$rec['is_active']) {
            $this->flash('error', 'ລາຍການຊ້ຳນີ້ຖືກປິດຢູ່. ເປີດກ່ອນຈຶ່ງສ້າງໄດ້.');
            $this->redirect('recurring');
        }

        // ✅ ກວດ end_date — ຖ້າໝົດກຳນົດ ຫ້າມ generate
        if (!empty($rec['end_date']) && date('Y-m-d') > $rec['end_date']) {
            $this->flash('error', 'ລາຍການຊ້ຳນີ້ໝົດກຳນົດແລ້ວ (' . date('d/m/Y', strtotime($rec['end_date'])) . ').');
            $this->redirect('recurring');
        }

        $this->requirePermission('recurring.generate', 'recurring');
        $status = $this->can('transactions.approve') ? 'approved' : 'pending';

        $this->transaction->create([
            'type'        => $rec['type'],
            'amount'      => $rec['amount'],
            'description' => $rec['description'],
            'category_id' => $rec['category_id'],
            'date'        => date('Y-m-d'),
            'notes'       => 'ສ້າງຈາກລາຍການຊ້ຳ: ' . $rec['description'],
            'status'      => $status,
            'created_by'  => $_SESSION['user_id'],
        ]);

        $next = $this->recurring->calcNextRun(
            $rec['frequency'],
            date('Y-m-d'),
            (int)($rec['day_of_month'] ?? 1)
        );
        $this->recurring->updateNextRun($id, $next);

        $this->flash('success', 'ສ້າງລາຍການສຳເລັດ. ຄັ້ງຕໍ່ໄປ: ' . date('d/m/Y', strtotime($next)) . '.');
        $this->redirect('recurring');
    }

    /** POST: ລຶບລາຍການຊ້ຳ */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('recurring');
        }
        $this->verifyCsrf();
        $this->requirePermission('recurring.delete', 'recurring');

        $this->recurring->delete($id);
        $this->flash('success', 'ລຶບລາຍການຊ້ຳສຳເລັດ.');
        $this->redirect('recurring');
    }
}