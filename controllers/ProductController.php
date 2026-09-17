<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

class ProductController {
    private const MAX_IMAGES = 5;
    private const MAX_IMAGE_SIZE = 5242880;

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

    private function validateImageUploads(int $availableSlots): array {
        $input = $_FILES['images'] ?? null;
        if (!$input || !isset($input['name']) || !is_array($input['name'])) {
            return [[], []];
        }

        $files = [];
        $errors = [];
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        foreach ($input['name'] as $index => $originalName) {
            $error = (int) ($input['error'][$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = 'Không thể tải ảnh ' . basename((string) $originalName) . '.';
                continue;
            }

            $tmpName = (string) ($input['tmp_name'][$index] ?? '');
            $size = (int) ($input['size'][$index] ?? 0);
            if ($size <= 0 || $size > self::MAX_IMAGE_SIZE) {
                $errors[] = 'Mỗi ảnh phải có dung lượng tối đa 5 MB.';
                continue;
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
            if (!is_string($mime) || !isset($allowedTypes[$mime])) {
                $errors[] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.';
                continue;
            }

            $files[] = ['tmp_name' => $tmpName, 'extension' => $allowedTypes[$mime]];
        }

        if (count($files) > $availableSlots) {
            $errors[] = 'Mỗi sản phẩm được lưu tối đa ' . self::MAX_IMAGES . ' ảnh.';
        }

        return [$files, $errors];
    }

    private function storeImageUploads(array $files, string $slug): array {
        if ($files === []) {
            return [];
        }

        $uploadDirectory = __DIR__ . '/../uploads';
        if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
            throw new RuntimeException('Thư mục uploads không tồn tại hoặc không có quyền ghi.');
        }

        $storedPaths = [];
        try {
            foreach ($files as $file) {
                $filename = 'upload-' . ($slug !== '' ? $slug : 'product') . '-'
                    . bin2hex(random_bytes(6)) . '.' . $file['extension'];
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new RuntimeException('Không thể lưu ảnh đã tải lên.');
                }
                $storedPaths[] = $filename;
            }
        } catch (Throwable $e) {
            $this->removeUploadedFiles($storedPaths);
            throw $e;
        }

        return $storedPaths;
    }

    private function removeUploadedFiles(array $paths): void {
        foreach ($paths as $path) {
            $filename = basename((string) $path);
            if (!str_starts_with($filename, 'upload-')) {
                continue;
            }
            $fullPath = __DIR__ . '/../uploads/' . $filename;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
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
            $description = Security::sanitizeRichText((string) ($_POST['description'] ?? ''));
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

            [$validImages, $imageErrors] = $this->validateImageUploads(self::MAX_IMAGES);
            $errors = array_merge($errors, $imageErrors);

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

            $uploadedPaths = [];
            try {
                $uploadedPaths = $this->storeImageUploads($validImages, $slug);
                $this->productModel->insertProduct($data, $uploadedPaths);
            } catch (Throwable $e) {
                $this->removeUploadedFiles($uploadedPaths);
                error_log($e->__toString());
                $errors[] = 'Không thể lưu sản phẩm và hình ảnh. Vui lòng thử lại.';
                $categories = $this->productModel->getAllCategories();
                require __DIR__ . '/../views/admin/products/create.php';
                return;
            }
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
        $productImages = $this->productModel->getProductImages($id);
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
            $description = Security::sanitizeRichText((string) ($_POST['description'] ?? ''));
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

            $productImages = $this->productModel->getProductImages($id);
            $ownedImageIds = array_map('intval', array_column($productImages, 'id'));
            $deleteImageIds = array_values(array_intersect(
                $ownedImageIds,
                array_map('intval', (array) ($_POST['delete_image_ids'] ?? []))
            ));
            $availableSlots = self::MAX_IMAGES - (count($productImages) - count($deleteImageIds));
            [$validImages, $imageErrors] = $this->validateImageUploads(max(0, $availableSlots));
            $errors = array_merge($errors, $imageErrors);

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

            $uploadedPaths = [];
            try {
                $uploadedPaths = $this->storeImageUploads($validImages, $slug);
                $deletedPaths = $this->productModel->updateProduct($id, $data, $uploadedPaths, $deleteImageIds);
                $this->removeUploadedFiles($deletedPaths);
            } catch (Throwable $e) {
                $this->removeUploadedFiles($uploadedPaths);
                error_log($e->__toString());
                $errors[] = 'Không thể cập nhật sản phẩm và hình ảnh. Vui lòng thử lại.';
                $categories = $this->productModel->getAllCategories();
                $productImages = $this->productModel->getProductImages($id);
                require __DIR__ . '/../views/admin/products/edit.php';
                return;
            }
            header("Location: index.php?action=product-index&msg=" . urlencode("Cập nhật sản phẩm thành công!"));
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0 && $this->productModel->getProductById($id)) {
                $productImages = $this->productModel->getProductImages($id);
                $this->productModel->deleteProduct($id);
                $this->removeUploadedFiles(array_column($productImages, 'image_path'));
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
