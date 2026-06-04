<?php
class DonationsController extends Controller
{
    private Donor $donor;
    private Transaction $transaction;
    private Account $account;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAnyPermission(['transactions.view_all', 'transactions.view_own', 'transactions.create'], '');
        $this->donor = new Donor();
        $this->transaction = new Transaction();
        $this->account = new Account();
    }

    public function index(): void
    {
        $donors = $this->donor->getAll();

        $this->view('donations/index', [
            'donors' => $donors
        ], 'ລາຍຊື່ຜູ້ບໍລິຈາກ — ' . APP_NAME, 'donations');
    }

    public function store_donor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('donations');
        $this->verifyCsrf();
        $this->requirePermission('transactions.create', 'donations');

        $data = [
            'name'    => htmlspecialchars(trim($_POST['name'] ?? '')),
            'phone'   => htmlspecialchars(trim($_POST['phone'] ?? '')),
            'address' => htmlspecialchars(trim($_POST['address'] ?? ''))
        ];

        if (empty($data['name'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ຜູ້ບໍລິຈາກ.');
            $this->redirect('donations');
        }

        $this->donor->create($data);
        $this->flash('success', 'ເພີ່ມຜູ້ບໍລິຈາກສຳເລັດ.');
        $this->redirect('donations');
    }

    public function update_donor(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('donations');
        $this->verifyCsrf();
        $this->requirePermission('transactions.create', 'donations');

        $data = [
            'name'    => htmlspecialchars(trim($_POST['name'] ?? '')),
            'phone'   => htmlspecialchars(trim($_POST['phone'] ?? '')),
            'address' => htmlspecialchars(trim($_POST['address'] ?? ''))
        ];

        if (empty($data['name'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່.');
            $this->redirect('donations');
        }

        $this->donor->update($id, $data);
        $this->flash('success', 'ແກ້ໄຂຜູ້ບໍລິຈາກສຳເລັດ.');
        $this->redirect('donations');
    }
    
    public function receipt(int $transaction_id): void
    {
        $this->requireAnyPermission(['transactions.view_all', 'transactions.view_own'], 'transactions');
        $tx = $this->transaction->getById($transaction_id);
        if (!$tx || empty($tx['donor_id'])) {
            $this->flash('error', 'ບໍ່ພົບຂໍ້ມູນໃບບິນບໍລິຈາກ.');
            $this->redirect('transactions');
        }
        
        // Load receipt view (without standard layout, or with a printable layout)
        $this->view('donations/receipt', [
            'transaction' => $tx
        ], 'ໃບບິນຮັບເງິນ — ' . APP_NAME, 'none');
    }
}
