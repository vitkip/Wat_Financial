<?php
class CategoriesController extends Controller
{
    private Category $category;

    public function __construct()
    {
        $this->requireAuth();
        $this->category = new Category();
    }

    public function index(): void
    {
        $this->view('categories/index', [
            'categories' => $this->category->getWithStats(),
        ], 'ໝວດໝູ່ — ' . APP_NAME, 'categories');
    }

    /** POST: ສ້າງໝວດໝູ່ໃໝ່ */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
        }
        $this->verifyCsrf();

        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#006C49';
        $icon  = htmlspecialchars(trim($_POST['icon']  ?? 'tag'));

        if (empty($name)) {
            $this->flash('error', 'ກະລຸນາໃສ່ຊື່ໝວດໝູ່.');
            $this->redirect('categories');
        }

        $this->category->create(['name' => $name, 'color' => $color, 'icon' => $icon]);
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

        $id    = (int)($_POST['id'] ?? 0);
        $name  = htmlspecialchars(trim($_POST['name']  ?? ''));
        $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#006C49';
        $icon  = htmlspecialchars(trim($_POST['icon']  ?? 'tag'));

        if (!$id || empty($name)) {
            $this->flash('error', 'ຂໍ້ມູນບໍ່ຄົບ ຫຼື ບໍ່ຖືກຕ້ອງ.');
            $this->redirect('categories');
        }

        $this->category->update($id, ['name' => $name, 'color' => $color, 'icon' => $icon]);
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

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $this->flash('error', 'ບໍ່ລະບຸໝວດໝູ່.');
            $this->redirect('categories');
        }

        $this->category->delete($id);
        $this->flash('success', 'ລຶບໝວດໝູ່ສຳເລັດ.');
        $this->redirect('categories');
    }
}
