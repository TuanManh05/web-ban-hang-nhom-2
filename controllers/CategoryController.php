<?php
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

class CategoryController {
    private $categoryModel;

    public function __construct($pdo) {
        Security::startSession();

        AuthMiddleware::requireAdmin();
        $this->categoryModel = new CategoryModel($pdo);
    }

    private function createSlug($value) {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $value = strtr($value, [
            'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
            'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
            'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
            'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
            'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d'
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim($value, '-');
    }

    private function generateUniqueSlug($name, $excludeId = null) {
        $baseSlug = $this->createSlug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'danh-muc';
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->categoryModel->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }

    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../views/admin/categories/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../views/admin/categories/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=category-index&error=' . urlencode('Phương thức không được hỗ trợ!'));
            exit;
        }

        Security::verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        $status = (int)($_POST['status'] ?? 1);
        $errors = [];

        if ($name === '') {
            $errors[] = "Tên danh mục không được để trống.";
        }
        if (!in_array($status, [0, 1], true)) {
            $errors[] = "Trạng thái danh mục không hợp lệ.";
        }

        if (empty($errors)) {
            $slug = $this->generateUniqueSlug($name);
            $this->categoryModel->insertCategory([
                'name' => $name,
                'slug' => $slug,
                'status' => $status
            ]);
            header('Location: index.php?action=category-index&msg=' . urlencode('Thêm danh mục thành công!'));
            exit;
        }

        require_once __DIR__ . '/../views/admin/categories/create.php';
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header("Location: index.php?action=category-index&error=Danh mục không tồn tại!");
            exit;
        }
        require_once __DIR__ . '/../views/admin/categories/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=category-index&error=' . urlencode('Phương thức không được hỗ trợ!'));
            exit;
        }

        Security::verifyCsrf();

        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header("Location: index.php?action=category-index&error=Danh mục không tồn tại!");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $status = (int)($_POST['status'] ?? 1);
        $errors = [];

        if ($name === '') {
            $errors[] = "Tên danh mục không được để trống.";
        }
        if (!in_array($status, [0, 1], true)) {
            $errors[] = "Trạng thái danh mục không hợp lệ.";
        }

        if (empty($errors)) {
            $slug = $name !== $category['name']
                ? $this->generateUniqueSlug($name, $id)
                : $category['slug'];
            $this->categoryModel->updateCategory($id, [
                'name' => $name,
                'slug' => $slug,
                'status' => $status
            ]);
            header('Location: index.php?action=category-index&msg=' . urlencode('Cập nhật danh mục thành công!'));
            exit;
        }

        require_once __DIR__ . '/../views/admin/categories/edit.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=category-index&error=' . urlencode('Phương thức không được hỗ trợ!'));
            exit;
        }

        Security::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $category = $this->categoryModel->getCategoryById($id);

        if (!$category) {
            header('Location: index.php?action=category-index&error=' . urlencode('Danh mục không tồn tại!'));
            exit;
        }

        if ($this->categoryModel->hasProducts($id)) {
            header('Location: index.php?action=category-index&error=' . urlencode('Không thể xóa danh mục đang có sản phẩm!'));
            exit;
        }

        $this->categoryModel->deleteCategory($id);
        header('Location: index.php?action=category-index&msg=' . urlencode('Xóa danh mục thành công!'));
        exit;
    }
}
