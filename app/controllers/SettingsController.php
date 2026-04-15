<?php
class SettingsController extends Controller
{
    private Setting $setting;
    private User    $user;

    public function __construct()
    {
        $this->requireAuth();
        $this->setting = new Setting();
        $this->user    = new User();
    }

    public function index(): void
    {
        $this->view('settings/index', [
            'settings' => $this->setting->getAll(),
            'user'     => $this->user->getById((int)$_SESSION['user_id']),
        ], 'ການຕັ້ງຄ່າ — ' . APP_NAME, 'settings');
    }

    /** POST: ບັນທຶກການຕັ້ງຄ່າ */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings');
        }
        $this->verifyCsrf();

        $allowed = [
            'app_name', 'currency_symbol', 'currency_code',
            'locale', 'timezone', 'date_format',
            'week_start', 'decimal_places', 'items_per_page', 'theme_color',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = htmlspecialchars(trim($_POST[$key]));
            }
        }

        $this->setting->updateMany($data);
        $this->flash('success', 'ບັນທຶກການຕັ້ງຄ່າສຳເລັດ.');
        $this->redirect('settings');
    }

    /** POST: ອັບເດດໂປຣໄຟລ໌ */
    public function profile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings');
        }
        $this->verifyCsrf();

        $id    = (int)$_SESSION['user_id'];
        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ ແລະ email.');
            $this->redirect('settings');
        }

        // ✅ Validate email format
        if (!$this->isValidEmail($email)) {
            $this->flash('error', 'ຮູບແບບ email ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('settings');
        }

        $this->user->updateProfile($id, ['name' => $name, 'email' => $email]);
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        $this->flash('success', 'ອັບເດດໂປຣໄຟລ໌ສຳເລັດ.');
        $this->redirect('settings');
    }

    /** POST: ປ່ຽນລະຫັດຜ່ານ */
    public function password(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('settings');
        }
        $this->verifyCsrf();

        $id      = (int)$_SESSION['user_id'];
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password']      ?? '';
        $confirm = $_POST['confirm_password']  ?? '';

        if ($newPass !== $confirm) {
            $this->flash('error', 'ລະຫັດຜ່ານໃໝ່ ແລະ ຢືນຢັນ ບໍ່ຕົງກັນ.');
            $this->redirect('settings');
        }

        if (strlen($newPass) < 6) {
            $this->flash('error', 'ລະຫັດຜ່ານໃໝ່ຕ້ອງມີຢ່າງໜ້ອຍ 6 ຕົວອັກສອນ.');
            $this->redirect('settings');
        }

        // ✅ Fix: ດຶງທຸກ field ລວມ password ດ້ວຍ query ດ່ຽວ
        $userWithPass = Database::getInstance()->fetch(
            "SELECT * FROM users WHERE id = :id AND is_active = 1",
            [':id' => $id]
        );

        if (!$userWithPass || !password_verify($current, $userWithPass['password'])) {
            $this->flash('error', 'ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ.');
            $this->redirect('settings');
        }

        $this->user->updatePassword($id, $newPass);

        // ── Regenerate session ID ຫຼັງປ່ຽນ password ─────────────
        // ກັນ session fixation: session ID ເກົ່າຈະໃຊ້ບໍ່ໄດ້ອີກ
        session_regenerate_id(true);

        $this->flash('success', 'ປ່ຽນລະຫັດຜ່ານສຳເລັດ. ກະລຸນາ login ໃໝ່ເພື່ອຄວາມປອດໄພ.');
        $this->redirect('settings');
    }
}