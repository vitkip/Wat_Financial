<?php
class UsersController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->requireAuth();
        $this->user = new User();
    }

    public function index(): void
    {
        $this->view('users/index', [
            'users' => $this->user->getAll(),
        ], 'ຜູ້ໃຊ້ — ' . APP_NAME, 'users');
    }

    /** POST: ສ້າງຜູ້ໃຊ້ໃໝ່ */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->verifyCsrf();

        $name     = htmlspecialchars(trim($_POST['name']     ?? ''));
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || strlen($password) < 6) {
            $this->flash('error', 'ກະລຸນາຕື່ມຂໍ້ມູນໃຫ້ຄົບ (ລະຫັດຜ່ານ ≥ 6 ຕົວ).');
            $this->redirect('users');
        }

        if (!$this->isValidEmail($email)) {
            $this->flash('error', 'ຮູບແບບ email ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('users');
        }

        try {
            $this->user->create(['name' => $name, 'email' => $email, 'password' => $password]);
            $this->flash('success', 'ສ້າງຜູ້ໃຊ້ໃໝ່ສຳເລັດ.');
        } catch (PDOException $e) {
            // MySQL error 1062 = Duplicate entry (UNIQUE constraint on email)
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate')) {
                $this->flash('error', 'Email ນີ້ຖືກໃຊ້ງານແລ້ວ. ກະລຸນາໃຊ້ email ອື່ນ.');
            } else {
                $this->flash('error', 'ເກີດຂໍ້ຜິດພາດ. ກະລຸນາລອງໃໝ່.');
            }
        }
        $this->redirect('users');
    }

    /** POST: ແກ້ໄຂໂປຣໄຟລ໌ */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->verifyCsrf();

        $id    = (int)($_POST['id'] ?? 0);
        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $email = trim($_POST['email'] ?? '');

        if (!$id || empty($name) || empty($email)) {
            $this->flash('error', 'ຂໍ້ມູນບໍ່ຄົບ.');
            $this->redirect('users');
        }

        if (!$this->isValidEmail($email)) {
            $this->flash('error', 'ຮູບແບບ email ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('users');
        }

        $this->user->updateProfile($id, ['name' => $name, 'email' => $email]);

        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
        }

        $this->flash('success', 'ອັບເດດຜູ້ໃຊ້ສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: reset ລະຫັດຜ່ານ */
    public function resetpassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->verifyCsrf();

        $id       = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if (!$id || strlen($password) < 6) {
            $this->flash('error', 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ 6 ຕົວ.');
            $this->redirect('users');
        }

        $this->user->updatePassword($id, $password);
        $this->flash('success', 'ປ່ຽນລະຫັດຜ່ານສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: ເປີດ/ປິດ ຜູ້ໃຊ້ */
    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        // ຫ້າມປິດຕົວເອງ
        if (!$id || $id === (int)$_SESSION['user_id']) {
            $this->flash('error', 'ບໍ່ສາມາດປ່ຽນສະຖານະຂອງຕົວເອງໄດ້.');
            $this->redirect('users');
        }

        $this->user->toggleActive($id);
        $this->flash('success', 'ອັບເດດສະຖານະຜູ້ໃຊ້ສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: ລຶບຜູ້ໃຊ້ */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        // ຫ້າມລຶບຕົວເອງ
        if (!$id || $id === (int)$_SESSION['user_id']) {
            $this->flash('error', 'ບໍ່ສາມາດລຶບບັນຊີຂອງຕົວເອງໄດ້.');
            $this->redirect('users');
        }

        $this->user->delete($id);
        $this->flash('success', 'ລຶບຜູ້ໃຊ້ສຳເລັດ.');
        $this->redirect('users');
    }
}
