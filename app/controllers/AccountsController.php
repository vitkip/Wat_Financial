<?php
class AccountsController extends Controller
{
    private Account $account;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('accounts.view', '');
        $this->account = new Account();
    }

    public function index(): void
    {

        $accounts = $this->account->getAll();

        $this->view('accounts/index', [
            'accounts' => $accounts
        ], 'ບັນຊີການເງິນ — ' . APP_NAME, 'accounts');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('accounts');
        $this->verifyCsrf();
        $this->requirePermission('accounts.create', 'accounts');

        $data = [
            'name'        => htmlspecialchars(trim($_POST['name'] ?? '')),
            'type'        => in_array($_POST['type'] ?? '', ['asset','liability','equity','revenue','expense'], true) ? $_POST['type'] : 'asset',
            'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];

        if (empty($data['name'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ບັນຊີ.');
            $this->redirect('accounts');
        }

        $this->account->create($data);
        $this->flash('success', 'ເພີ່ມບັນຊີສຳເລັດ.');
        $this->redirect('accounts');
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('accounts');
        $this->verifyCsrf();
        $this->requirePermission('accounts.edit', 'accounts');

        $data = [
            'name'        => htmlspecialchars(trim($_POST['name'] ?? '')),
            'type'        => in_array($_POST['type'] ?? '', ['asset','liability','equity','revenue','expense'], true) ? $_POST['type'] : 'asset',
            'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];

        if (empty($data['name'])) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ບັນຊີ.');
            $this->redirect('accounts');
        }

        $this->account->update($id, $data);
        $this->flash('success', 'ແກ້ໄຂບັນຊີສຳເລັດ.');
        $this->redirect('accounts');
    }
}
