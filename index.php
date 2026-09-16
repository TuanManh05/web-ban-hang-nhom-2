<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e): void {
    error_log($e->__toString());
    http_response_code(500);
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8">'
        . '<title>Lỗi hệ thống</title></head>'
        . '<body style="font-family:sans-serif;text-align:center;padding:60px;">'
        . '<h2>Đã có lỗi xảy ra</h2>'
        . '<p>Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>'
        . '<a href="index.php">Về trang chủ</a></body></html>';
});

require_once __DIR__ . '/middleware/Security.php';
Security::startSession();

// Nạp các file cấu hình và Controller cần thiết
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CategoryController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/AccountController.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Lấy kết nối PDO từ hàm database()
$pdo = database();

// Lấy tham số action từ URL (mặc định quay về 'home')
$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'register':
        (new AuthController($pdo))->showRegister();
        break;

    case 'register-submit':
        (new AuthController($pdo))->register();
        break;

    case 'login':
        (new AuthController($pdo))->showLogin();
        break;

    case 'login-submit':
        (new AuthController($pdo))->login();
        break;

    case 'logout':
        (new AuthController($pdo))->logout();
        break;

    case 'order-store':
        (new OrderController($pdo))->store();
        break;

    case 'orders':
        (new OrderController($pdo))->history();
        break;
    case 'order-detail':
        (new OrderController($pdo))->detail();
        break;
    case 'order-cancel':
        (new OrderController($pdo))->cancel();
        break;
    case 'admin-orders':
        (new OrderController($pdo))->adminIndex();
        break;
    case 'admin-order-detail':
        (new OrderController($pdo))->adminDetail();
        break;
    case 'order-invoice':
        (new OrderController($pdo))->invoice();
        break;
    case 'admin-order-invoice':
        (new OrderController($pdo))->adminInvoice();
        break;
    case 'admin-order-status':
        (new OrderController($pdo))->updateStatus();
        break;

    case 'profile':
        (new AccountController($pdo))->profile();
        break;
    case 'profile-update':
        (new AccountController($pdo))->updateProfile();
        break;
    case 'password-update':
        (new AccountController($pdo))->changePassword();
        break;

    case 'checkout':
        require_once __DIR__ . '/views/checkout.php';
        break;

    case 'policy':
        require_once __DIR__ . '/views/policy.php';
        break;

    // ==========================================
    // 0. Trang Dashboard Quản trị trung tâm (Admin Index)
    // ==========================================
    case 'admin':
        AuthMiddleware::requireAdmin();
        require_once __DIR__ . '/views/admin/index.php';
        break;
    case 'admin-stats':
        AuthMiddleware::requireAdmin();
        require_once __DIR__ .'/views/admin/stats.php';
        break ; 
    

    // ==========================================
    // 1. Luồng Quản lý Sản phẩm (Product CRUD)
    // ==========================================
    case 'product-index':
    case 'products':
        $controller = new ProductController($pdo);
        $controller->index();
        break;

    case 'product-create':
        $controller = new ProductController($pdo);
        $controller->create();
        break;

    case 'product-store':
        $controller = new ProductController($pdo);
        $controller->store();
        break;

    case 'product-edit':
        $controller = new ProductController($pdo);
        $controller->edit();
        break;

    case 'product-update':
        $controller = new ProductController($pdo);
        $controller->update();
        break;

    case 'product-delete':
        $controller = new ProductController($pdo);
        $controller->delete();
        break;

    // ==========================================
    // 2. Luồng Quản lý Danh mục (Category CRUD)
    // ==========================================
    case 'category-index':
    case 'categories':
        $controller = new CategoryController($pdo);
        $controller->index();
        break;

    case 'category-create':
        $controller = new CategoryController($pdo);
        $controller->create();
        break;

    case 'category-store':
        $controller = new CategoryController($pdo);
        $controller->store();
        break;

    case 'category-edit':
        $controller = new CategoryController($pdo);
        $controller->edit();
        break;

    case 'category-update':
        $controller = new CategoryController($pdo);
        $controller->update();
        break;

    case 'category-delete':
        $controller = new CategoryController($pdo);
        $controller->delete();
        break;

    // ==========================================
    // 3. Trang chủ ứng dụng (Default)
    // ==========================================
    case 'home':
    default:
        $controller = new HomeController();
        $controller->index();
        break;
}
