<?php
class GoalsController extends Controller
{
    private SavingsGoal $goal;

    public function __construct()
    {
        $this->requireAuth();
        $this->goal = new SavingsGoal();
    }

    public function index(): void
    {
        $this->view('goals/index', [
            'goals'      => $this->goal->getAll(),
            'totalSaved' => $this->goal->getTotalSaved(),
        ], 'ເປົ້າໝາຍ — ' . APP_NAME, 'goals');
    }

    /** POST: ສ້າງເປົ້າໝາຍໃໝ່ */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('goals');
        }
        $this->verifyCsrf();

        $data = [
            'name'           => htmlspecialchars(trim($_POST['name'] ?? '')),
            'description'    => htmlspecialchars(trim($_POST['description'] ?? '')),
            'target_amount'  => (float)($_POST['target_amount']  ?? 0),
            'current_amount' => max(0, (float)($_POST['current_amount'] ?? 0)), // ✅ ກັນ negative
            'target_date'    => !empty($_POST['target_date']) ? $_POST['target_date'] : null,
            'color'          => $_POST['color'] ?? '#006C49',
            'icon'           => $_POST['icon']  ?? 'target',
        ];

        if (empty($data['name']) || $data['target_amount'] <= 0) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ ແລະ ຈຳນວນເປົ້າໝາຍ.');
            $this->redirect('goals');
        }

        // ✅ current_amount ຕ້ອງ <= target_amount
        if ($data['current_amount'] > $data['target_amount']) {
            $data['current_amount'] = $data['target_amount'];
        }

        $this->goal->create($data);
        $this->flash('success', 'ສ້າງເປົ້າໝາຍສຳເລັດ.');
        $this->redirect('goals');
    }

    /** POST: ເພີ່ມເງິນໃຫ້ເປົ້າໝາຍ */
    public function add(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('goals');
        }
        $this->verifyCsrf();

        $amount = (float)($_POST['amount'] ?? 0);

        if ($amount <= 0) {
            $this->flash('error', 'ກະລຸນາໃສ່ຈຳນວນທີ່ຖືກຕ້ອງ (> 0).');
            $this->redirect('goals');
        }

        $this->goal->addAmount($id, $amount);
        $this->flash('success', 'ເພີ່ມເງິນສຳເລັດ.');
        $this->redirect('goals');
    }

    /** POST: ລຶບເປົ້າໝາຍ */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('goals');
        }
        $this->verifyCsrf();

        $this->goal->delete($id);
        $this->flash('success', 'ລຶບເປົ້າໝາຍສຳເລັດ.');
        $this->redirect('goals');
    }
}