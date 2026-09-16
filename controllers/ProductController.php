<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

class ProductController {
    private $productModel;

    public function __construct($pdo) {
        Security::startSession();

        AuthMiddleware::requireAdmin();

        $this->productModel = new ProductModel($pdo);
    }

    private function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }

    private function generateUniqueSlug($name, $excludeId = null) {
        $baseSlug = $this->createSlug($name);
        $slug = $baseSlug;
        $count = 1;

        while ($this->productModel->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        require_once __DIR__ . '/../views/admin/products/index.php';
    }

    public function create() {
        $categories = $this->productModel->getAllCategories();
        require_once __DIR__ . '/../views/admin/products/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $name        = trim($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price       = $_POST['price'] ?? '';
            $stock       = $_POST['stock'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

            $errors = [];

            if (empty($name)) {
                $errors[] = "Tên sản phẩm không được để trống.";
            }

            if ($category_id <= 0) {
                $errors[] = "Vui lòng chọn danh mục hợp lệ.";
            } else {
                $categoryExists = $this->productModel->getCategoryById($category_id);
                if (!$categoryExists) {
                    $errors[] = "Danh mục được chọn không tồn tại trong hệ thống.";
                }
            }

            if (!is_numeric($price) || (float)$price <= 0) {
                $errors[] = "Giá sản phẩm phải là số và lớn hơn 0.";
            }
            if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock !== '0') {
                $errors[] = "Số lượng phải là số nguyên.";
            } elseif ((int)$stock < 0) {
                $errors[] = "Số lượng phải lớn hơn hoặc bằng 0.";
            }
            if (!in_array($status, [0, 1], true)) {
                $errors[] = "Trạng thái sản phẩm không hợp lệ.";
            }

            if (!empty($errors)) {
                $categories = $this->productModel->getAllCategories();
                require_once __DIR__ . '/../views/admin/products/create.php';
                return;
            }

            $slug = $this->generateUniqueSlug($name);

            $data = [
                'category_id' => $category_id,
                'name'        => $name,
                'slug'        => $slug,
                'price'       => (float)$price,
                'stock'       => (int)$stock,
                'description' => $description,
                'status'      => $status
            ];

            $this->productModel->insertProduct($data);
            header("Location: index.php?action=product-index&msg=" . urlencode("Thêm sản phẩm thành công!"));
            exit;
        }
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            header("Location: index.php?action=product-index&error=" . urlencode("Sản phẩm không tồn tại!"));
            exit;
        }

        $categories = $this->productModel->getAllCategories();
        require_once __DIR__ . '/../views/admin/products/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $id          = (int)($_GET['id'] ?? 0);
            $product     = $this->productModel->getProductById($id);

            if (!$product) {
                header("Location: index.php?action=product-index&error=" . urlencode("Sản phẩm không tồn tại!"));
                exit;
            }

            $name        = trim($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price       = $_POST['price'] ?? '';
            $stock       = $_POST['stock'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

            $errors = [];

            if (empty($name)) {
                $errors[] = "Tên sản phẩm không được để trống.";
            }

            if ($category_id <= 0) {
                $errors[] = "Vui lòng chọn danh mục hợp lệ.";
            } else {
                $categoryExists = $this->productModel->getCategoryById($category_id);
                if (!$categoryExists) {
                    $errors[] = "Danh mục được chọn không tồn tại trong hệ thống.";
                }
            }

            if (!is_numeric($price) || (float)$price <= 0) {
                $errors[] = "Giá sản phẩm phải là số và lớn hơn 0.";
            }
            if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock !== '0') {
                $errors[] = "Số lượng phải là số nguyên.";
            } elseif ((int)$stock < 0) {
                $errors[] = "Số lượng phải lớn hơn hoặc bằng 0.";
            }
            if (!in_array($status, [0, 1], true)) {
                $errors[] = "Trạng thái sản phẩm không hợp lệ.";
            }

            if (!empty($errors)) {
                $categories = $this->productModel->getAllCategories();
                require_once __DIR__ . '/../views/admin/products/edit.php';
                return;
            }

            $slug = ($name !== $product['name']) 
                ? $this->generateUniqueSlug($name, $id) 
                : $product['slug'];

            $data = [
                'category_id' => $category_id,
                'name'        => $name,
                'slug'        => $slug,
                'price'       => (float)$price,
                'stock'       => (int)$stock,
                'description' => $description,
                'status'      => $status
            ];

            $this->productModel->updateProduct($id, $data);
            header("Location: index.php?action=product-index&msg=" . urlencode("Cập nhật sản phẩm thành công!"));
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0 && $this->productModel->getProductById($id)) {
                $this->productModel->deleteProduct($id);
                header("Location: index.php?action=product-index&msg=" . urlencode("Xóa sản phẩm thành công!"));
                exit;
            } else {
                header("Location: index.php?action=product-index&error=" . urlencode("Sản phẩm không tồn tại!"));
                exit;
            }
        }

        header("Location: index.php?action=product-index&error=" . urlencode("Phương thức không được hỗ trợ!"));
        exit;
    }
}
?>
