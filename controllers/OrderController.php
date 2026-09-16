<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

final class OrderController
{
    private Order $orders;

    public function __construct(PDO $pdo)
    {
        Security::startSession();

        $this->orders = new Order($pdo);
    }

    public function store(): void
    {
        Security::requirePost();
        Security::verifyCsrf();

        $customer = [
            'name' => trim((string) ($_POST['customer_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'note' => trim((string) ($_POST['note'] ?? '')),
        ];
        $errors = $this->validate($customer);

        if ($errors !== []) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_old'] = $customer;
            $this->redirectBack(implode(' ', $errors));
        }

        $cartItems = Cart::getItems();
        if ($cartItems === []) {
            $this->redirectBack('Giỏ hàng đang trống.');
        }

        try {
            $userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
            $orderId = $this->orders->createFromCart($userId, $customer, $cartItems);
            Cart::clear();
            unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);

            $order = $this->orders->find($orderId);
            $orderCode = 'DH' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
            $pageTitle = 'Đặt hàng thành công';
            require __DIR__ . '/../views/order-success.php';
        } catch (DomainException $exception) {
            $this->redirectBack($exception->getMessage());
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $this->redirectBack('Không thể tạo đơn hàng lúc này. Vui lòng thử lại.');
        }
    }

    public function history(): void
    {
        AuthMiddleware::requireLogin();
        $perPage = 10;
        $totalOrders = $this->orders->countForUser((int) $_SESSION['user']['id']);
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));
        $page = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
        $orders = $this->orders->getForUser((int) $_SESSION['user']['id'], $perPage, ($page - 1) * $perPage);
        $pageTitle = 'Lịch sử đơn hàng';
        require __DIR__ . '/../views/orders/history.php';
    }

    public function detail(): void
    {
        AuthMiddleware::requireLogin();
        $order = $this->orders->findForUser(max(0, (int) ($_GET['id'] ?? 0)), (int) $_SESSION['user']['id']);
        if (!$order) { http_response_code(404); }
        $pageTitle = $order ? 'Chi tiết đơn hàng' : 'Không tìm thấy đơn hàng';
        require __DIR__ . '/../views/orders/detail.php';
    }

    public function adminIndex(): void
    {
        AuthMiddleware::requireAdmin();
        $filters = ['q' => trim((string) ($_GET['q'] ?? '')), 'status' => (string) ($_GET['status'] ?? ''), 'sort' => (string) ($_GET['sort'] ?? '')];
        $perPage = 10;
        $totalOrders = $this->orders->countSearch($filters);
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));
        $page = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
        $orders = $this->orders->search($filters, $perPage, ($page - 1) * $perPage);
        $pageTitle = 'Quản lý đơn hàng';
        require __DIR__ . '/../views/admin/orders/index.php';
    }

    public function adminDetail(): void
    {
        AuthMiddleware::requireAdmin();
        $order = $this->orders->find(max(0, (int) ($_GET['id'] ?? 0)));
        if (!$order) { http_response_code(404); }
        $pageTitle = $order ? 'Chi tiết đơn hàng' : 'Không tìm thấy đơn hàng';
        require __DIR__ . '/../views/admin/orders/detail.php';
    }

    public function invoice(): void
    {
        AuthMiddleware::requireLogin();
        $order = $this->orders->findForUser(max(0, (int) ($_GET['id'] ?? 0)), (int) $_SESSION['user']['id']);
        if (!$order) { http_response_code(404); }
        $orderCode = $order ? 'DH' . str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) : '';
        $backUrl = $order ? ('index.php?action=order-detail&id=' . $order['id']) : 'index.php?action=orders';
        $pageTitle = 'Hóa đơn ' . $orderCode;
        require __DIR__ . '/../views/orders/invoice.php';
    }

    public function adminInvoice(): void
    {
        AuthMiddleware::requireAdmin();
        $order = $this->orders->find(max(0, (int) ($_GET['id'] ?? 0)));
        if (!$order) { http_response_code(404); }
        $orderCode = $order ? 'DH' . str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) : '';
        $backUrl = $order ? ('index.php?action=admin-order-detail&id=' . $order['id']) : 'index.php?action=admin-orders';
        $pageTitle = 'Hóa đơn ' . $orderCode;
        require __DIR__ . '/../views/orders/invoice.php';
    }

    public function updateStatus(): void
    {
        AuthMiddleware::requireAdmin();
        Security::requirePost();
        Security::verifyCsrf();
        $orderId = max(0, (int) ($_POST['order_id'] ?? 0));
        $updated = $this->orders->updateStatus($orderId, (string) ($_POST['status'] ?? ''));
        $_SESSION['flash'] = [
            'type' => $updated ? 'success' : 'danger',
            'text' => $updated ? 'Đã cập nhật trạng thái đơn hàng.' : 'Không thể cập nhật trạng thái đơn hàng.',
        ];
        header('Location: index.php?action=admin-order-detail&id=' . $orderId);
        exit;
    }

    public function cancel(): void
    {
        AuthMiddleware::requireLogin();
        Security::requirePost();
        Security::verifyCsrf();
        $orderId = max(0, (int) ($_POST['order_id'] ?? 0));
        try {
            $cancelled = $this->orders->cancelForUser($orderId, (int) $_SESSION['user']['id']);
            $_SESSION['flash'] = [
                'type' => $cancelled ? 'success' : 'danger',
                'text' => $cancelled
                    ? 'Đã hủy đơn hàng và hoàn lại tồn kho.'
                    : 'Chỉ có thể hủy đơn đang chờ xác nhận của bạn.',
            ];
            header('Location: index.php?action=order-detail&id=' . $orderId);
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $_SESSION['flash'] = [
                'type' => 'danger',
                'text' => 'Không thể hủy đơn lúc này.',
            ];
            header('Location: index.php?action=order-detail&id=' . $orderId);
        }
        exit;
    }

    private function validate(array $customer): array
    {
        $errors = [];

        if (mb_strlen($customer['name']) < 2 || mb_strlen($customer['name']) > 100) {
            $errors[] = 'Họ tên phải có từ 2 đến 100 ký tự.';
        }
        if (!preg_match('/^[0-9+().\s-]{8,20}$/', $customer['phone'])) {
            $errors[] = 'Số điện thoại không hợp lệ.';
        }
        if (mb_strlen($customer['address']) < 5 || mb_strlen($customer['address']) > 255) {
            $errors[] = 'Địa chỉ phải có từ 5 đến 255 ký tự.';
        }
        if (mb_strlen($customer['note']) > 500) {
            $errors[] = 'Ghi chú không được vượt quá 500 ký tự.';
        }

        return $errors;
    }

    private function redirectBack(string $message): never
    {
        $checkoutView = __DIR__ . '/../views/checkout.php';
        $target = is_file($checkoutView)
            ? 'index.php?action=checkout'
            : 'views/cart.php';

        $_SESSION['flash'] = [
            'type' => 'danger',
            'text' => $message,
        ];

        header('Location: ' . $target);
        exit;
    }
}
