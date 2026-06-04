<?php
class CategoriesController extends Controller
{
    private Category $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('categories.view', '');
        $this->category = new Category();
    }

    public function index(): void
    {
        $this->view('categories/index', [
            // Scope stats to the current year — avoids scanning all-time history
            'categories' => $this->category->getWithStats((int) date('Y')),
        ], 'ໝວດໝູ່ — ' . APP_NAME, 'categories');
    }

    /** POST: ສ້າງໝວດໝູ່ໃໝ່ */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
        }
        $this->verifyCsrf();
        $this->requirePermission('categories.create', 'categories');

        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#006C49';
        $icon  = htmlspecialchars(trim($_POST['icon']  ?? 'tag'));
        $type  = in_array($_POST['type'] ?? '', ['income', 'expense', 'both'], true)
                 ? $_POST['type'] : 'both';

        if (empty($name)) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ໝວດໝູ່.');
            $this->redirect('categories');
        }

        $this->category->create(['name' => $name, 'color' => $color, 'icon' => $icon, 'type' => $type]);
        $this->flash('success', 'ສ້າງໝວດໝູ່ສຳເລັດ.');
        $this->redirect('categories');
    }

    /** POST: ແກ້ໄຂໝວດໝູ່ */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
        }
        $this->verifyCsrf();
        $this->requirePermission('categories.edit', 'categories');

        $id    = (int)($_POST['id'] ?? 0);
        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#006C49';
        $icon  = htmlspecialchars(trim($_POST['icon']  ?? 'tag'));
        $type  = in_array($_POST['type'] ?? '', ['income', 'expense', 'both'], true)
                 ? $_POST['type'] : 'both';

        if (!$id || empty($name)) {
            $this->flash('error', 'ຂໍ້ມູນບໍ່ຄົບ ຫຼື ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('categories');
        }

        $this->category->update($id, ['name' => $name, 'color' => $color, 'icon' => $icon, 'type' => $type]);
        $this->flash('success', 'ອັບເດດໝວດໝູ່ສຳເລັດ.');
        $this->redirect('categories');
    }

    /** POST: ລຶບໝວດໝູ່ */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
        }
        $this->verifyCsrf();
        $this->requirePermission('categories.delete', 'categories');

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $this->flash('error', 'ບໍ່ລະບຸໝວດໝູ່.');
            $this->redirect('categories');
        }

        // Budgets FK uses ON DELETE CASCADE — deleting the category silently removes all
        // budget entries for it. Block the operation and tell the user to remove them first.
        $budgetCount = (int) (Database::getInstance()->fetch(
            "SELECT COUNT(*) AS cnt FROM budgets WHERE category_id = :id",
            [':id' => $id]
        )['cnt'] ?? 0);

        if ($budgetCount > 0) {
            $this->flash('error', "ບໍ່ສາມາດລຶບໝວດໝູ່ນີ້ໄດ້ ເພາະມີງົບປະມານ {$budgetCount} ລາຍການ. ກະລຸນາລຶບງົບກ່ອນ.");
            $this->redirect('categories');
        }

        $this->category->delete($id);
        $this->flash('success', 'ລຶບໝວດໝູ່ສຳເລັດ.');
        $this->redirect('categories');
    }
}
