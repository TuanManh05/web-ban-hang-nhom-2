<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Trang này có thể được mở qua index.php?action=checkout (URL ở gốc site),
// nên cần tự tính $basePath ở đây để dùng cho redirect phía dưới, trước khi
// header.php (nơi thường tính $basePath) được include.
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__ . '/..');
$basePath = '';
if ($documentRoot && $projectRoot && str_starts_with($projectRoot, $documentRoot)) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

$pageTitle = 'Thanh toán';

$items = Cart::getItems();

// Không cho thanh toán khi giỏ hàng trống
if (empty($items)) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'text' => 'Giỏ hàng đang trống, không thể thanh toán.',
    ];
    header('Location: ' . $basePath . '/views/cart.php');
    exit;
}

// OrderController chỉ kiểm tra csrf_token, không tự sinh — nếu chưa có thì tạo ở đây
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Lỗi & dữ liệu cũ do OrderController::store() lưu vào session trước khi redirect về
$errors = $_SESSION['checkout_errors'] ?? [];
$old = $_SESSION['checkout_old'] ?? [
    'name'    => '',
    'phone'   => '',
    'address' => '',
    'note'    => '',
];
unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);

$total = Cart::getTotalAmount();

require __DIR__ . '/partials/header.php';
?>
<section class="container py-5">
    <h1 class="h3 fw-bold mb-4">Thanh toán</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7 order-2 order-lg-1">
            <form method="post" action="<?= $basePath ?>/index.php?action=order-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="customer_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required
                           value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-control" required
                           value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ nhận hàng <span class="text-danger">*</span></label>
                    <input type="text" id="address" name="address" class="form-control" required
                           value="<?= htmlspecialchars($old['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-4">
                    <label for="note" class="form-label">Ghi chú (không bắt buộc)</label>
                    <textarea id="note" name="note" rows="3" class="form-control"><?= htmlspecialchars($old['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Đặt hàng</button>
            </form>
        </div>

        <div class="col-lg-5 order-1 order-lg-2">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Đơn hàng của bạn</h2>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                <div>
                                    <p class="mb-0 fw-medium"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <small class="text-secondary">
                                        <?= $item['quantity'] ?> x <?= number_format($item['price'], 0, ',', '.') ?> đ
                                    </small>
                                </div>
                                <span class="fw-semibold text-nowrap">
                                    <?= number_format($item['subtotal'], 0, ',', '.') ?> đ
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="d-flex justify-content-between border-top pt-3">
                        <span class="fw-bold">Tổng tiền</span>
                        <span class="fw-bold text-danger fs-5"><?= number_format($total, 0, ',', '.') ?> đ</span>
                    </div>
                </div>
            </div>
            <a href="<?= $basePath ?>/views/cart.php" class="btn btn-outline-secondary w-100 mt-3">&laquo; Quay lại giỏ hàng</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>