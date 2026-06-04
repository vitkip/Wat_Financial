<?php
class UsersController extends Controller
{
    private User $user;
    private Role $role;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('users.view', '');
        $this->user = new User();
        $this->role = new Role();
    }

    public function index(): void
    {
        $this->view('users/index', [
            'users' => $this->user->getAll(),
            'roles' => $this->role->getAll(),
        ], 'ຜູ້ໃຊ້ — ' . APP_NAME, 'users');
    }

    /** POST: ສ້າງຜູ້ໃຊ້ໃໝ່ */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.create', 'users');

        $name     = htmlspecialchars(trim($_POST['name']     ?? ''));
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $roleId   = (int) ($_POST['role_id'] ?? 0);

        if (empty($name) || empty($email) || strlen($password) < 8) {
            $this->flash('error', 'ກະລຸນາຕື່ມຂໍ້ມູນໃຫ້ຄົບ (ລະຫັດຜ່ານ ≥ 8 ຕົວ).');
            $this->redirect('users');
        }

        if (!$this->isValidEmail($email)) {
            $this->flash('error', 'ຮູບແບບ email ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('users');
        }

        // Only super_admin can create another super_admin
        $targetRole = $this->role->getById($roleId);
        if ($targetRole && $targetRole['name'] === 'super_admin' && !$this->can('users.manage_roles')) {
            $this->flash('error', 'ທ່ານບໍ່ມີສິດສ້າງ Super Admin.');
            $this->redirect('users');
        }

        try {
            $this->user->create([
                'name'    => $name,
                'email'   => $email,
                'password'=> $password,
                'role_id' => $roleId ?: null,
            ]);
            $this->flash('success', 'ສ້າງຜູ້ໃຊ້ໃໝ່ສຳເລັດ.');
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate')) {
                $this->flash('error', 'Email ນີ້ຖືກໃຊ້ງານແລ້ວ.');
            } else {
                $this->flash('error', 'ເກີດຂໍ້ຜິດພາດ. ກະລຸນາລອງໃໝ່.');
            }
        }
        $this->redirect('users');
    }

    /** POST: ແກ້ໄຂໂປຣໄຟລ໌ */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.edit', 'users');

        $id    = (int) ($_POST['id'] ?? 0);
        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $email = trim($_POST['email'] ?? '');

        if (!$id || empty($name) || empty($email) || !$this->isValidEmail($email)) {
            $this->flash('error', 'ຂໍ້ມູນບໍ່ຄົບ ຫຼື email ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('users');
        }

        $this->user->updateProfile($id, ['name' => $name, 'email' => $email]);

        if ($id === (int) $_SESSION['user_id']) {
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
        }

        $this->flash('success', 'ອັບເດດຜູ້ໃຊ້ສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: ປ່ຽນ Role ຂອງຜູ້ໃຊ້ */
    public function assignrole(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.manage_roles', 'users');

        $id     = (int) ($_POST['id']      ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if (!$id || !$roleId) {
            $this->flash('error', 'ຂໍ້ມູນບໍ່ຄົບ.');
            $this->redirect('users');
        }

        if ($id === (int) $_SESSION['user_id']) {
            $this->flash('error', 'ບໍ່ສາມາດປ່ຽນ Role ຂອງຕົວເອງໄດ້.');
            $this->redirect('users');
        }

        $this->user->updateRole($id, $roleId);
        $this->flash('success', 'ອັບເດດ Role ສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: reset ລະຫັດຜ່ານ */
    public function resetpassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.edit', 'users');

        $id       = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if (!$id || strlen($password) < 8) {
            $this->flash('error', 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ 8 ຕົວ.');
            $this->redirect('users');
        }

        $this->user->updatePassword($id, $password);
        $this->flash('success', 'ປ່ຽນລະຫັດຜ່ານສຳເລັດ.');
        $this->redirect('users');
    }

    /** POST: ເປີດ/ປິດ ຜູ້ໃຊ້ */
    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.edit', 'users');

        $id = (int) ($_POST['id'] ?? 0);

        if (!$id || $id === (int) $_SESSION['user_id']) {
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('users');
        $this->verifyCsrf();
        $this->requirePermission('users.delete', 'users');

        $id = (int) ($_POST['id'] ?? 0);

        if (!$id || $id === (int) $_SESSION['user_id']) {
            $this->flash('error', 'ບໍ່ສາມາດລຶບບັນຊີຂອງຕົວເອງໄດ້.');
            $this->redirect('users');
        }

        // Prevent removing the very last super_admin — system would become inaccessible.
        $target = $this->user->getById($id);
        if ($target && $target['role_name'] === 'super_admin') {
            $remaining = (int) (Database::getInstance()->fetch(
                "SELECT COUNT(*) AS cnt FROM users WHERE role = 'super_admin' AND is_active = 1"
            )['cnt'] ?? 0);
            if ($remaining <= 1) {
                $this->flash('error', 'ບໍ່ສາມາດລຶບ Super Admin ຄົນດຽວທີ່ຍັງເຫຼືອໃນລະບົບໄດ້.');
                $this->redirect('users');
            }
        }

        $this->user->delete($id);
        $this->flash('success', 'ລຶບຜູ້ໃຊ້ສຳເລັດ.');
        $this->redirect('users');
    }

    /** GET: ຕາຕະລາງ permission matrix (read-only) */
    public function permissions(): void
    {
        $this->requirePermission('users.manage_roles', 'users');
        $data = $this->role->getPermissionMatrix();
        $this->view('users/permissions', [
            'roles'  => $data['roles'],
            'matrix' => $data['matrix'],
        ], 'Permission Matrix — ' . APP_NAME, 'users');
    }
}
