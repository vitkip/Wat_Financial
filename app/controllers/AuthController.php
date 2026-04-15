<?php
class AuthController extends Controller
{
    private User $user;

    /** ຈຳກັດຄວາມພະຍາຍາມ login: 5 ຄັ້ງ / 10 ນາທີ */
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECS = 600; // 10 min

    public function __construct()
    {
        $this->user = new User();
    }

    /** ໜ້າ login */
    public function index(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }
        $this->view('auth/login', [], 'ເຂົ້າສູ່ລະບົບ — ' . APP_NAME, '', 'auth');
    }

    /** POST: ຮັບ credentials ແລ້ວ login */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth');
        }

        // ── Rate limiting via session ─────────────────────────
        $now = time();
        if (!empty($_SESSION['login_locked_until']) && $now < $_SESSION['login_locked_until']) {
            $wait = ceil(($_SESSION['login_locked_until'] - $now) / 60);
            $this->flash('error', "ທ່ານລອງຫຼາຍຄັ້ງເກີນໄປ. ກະລຸນາລໍຖ້າ {$wait} ນາທີ.");
            $this->redirect('auth');
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'ກະລຸນາໃສ່ email ແລະ ລະຫັດຜ່ານ.');
            $this->redirect('auth');
        }

        $user = $this->user->verify($email, $password);

        if (!$user) {
            // ນັບ attempt
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
                $_SESSION['login_locked_until'] = $now + self::LOCKOUT_SECS;
                $_SESSION['login_attempts']     = 0;
                $this->flash('error', 'ລອງຫຼາຍຄັ້ງຫຼາຍ. ບັນຊີຖືກລັອກ 10 ນາທີ.');
            } else {
                $left = self::MAX_ATTEMPTS - $_SESSION['login_attempts'];
                $this->flash('error', "Email ຫຼື ລະຫັດຜ່ານ ບໍ່ຖືກຕ້ອງ. ສາມາດລອງໄດ້ອີກ {$left} ຄັ້ງ.");
            }
            $this->redirect('auth');
        }

        // ── Login ສຳເລັດ — ລ້າງ attempt counter ───────────────
        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        $this->user->touchLogin((int)$user['id']);
        $this->redirect('');
    }

    /** ອອກຈາກລະບົບ */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        $this->redirect('auth');
    }
}