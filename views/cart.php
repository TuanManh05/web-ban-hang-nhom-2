<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../middleware/Security.php';

Security::startSession();

$pageTitle = 'Giỏ hàng';

// Đường dẫn tương đối của chính trang này, dùng để redirect sau khi xử lý form
$selfPath = 'cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf();
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $result = null;

    switch ($action) {    
        case 'add':
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            $result = Cart::add($productId, $quantity);
            break;

        case 'increase':
            $result = Cart::increase($productId);
            break;

        case 'decrease':
            $result = Cart::decrease($productId);
            break;

        case 'remove':
            Cart::remove($productId);
            $result = ['success' => true, 'message' => 'Đã xoá sản phẩm khỏi giỏ hàng.'];
            break;

        case 'clear':
            Cart::clear();
            $result = ['success' => true, 'message' => 'Đã xoá toàn bộ giỏ hàng.'];
            break;
    }

    // Cho phép các trang khác (vd. product-detail.php) chỉ định nơi quay lại
    // sau khi thêm vào giỏ, nhưng chỉ chấp nhận đường dẫn .php nội bộ để tránh
    // rủi ro open-redirect.
    $redirectTo = $selfPath;
    if (
        !empty($_POST['redirect'])
        && preg_match('#^/?[a-zA-Z0-9_\-]+(?:/[a-zA-Z0-9_\-]+)*\.php(?:\?[a-zA-Z0-9=&%._\-\[\]]*)?$#', (string) $_POST['redirect'])
    ) {
        $redirectTo = $_POST['redirect'];
    }

    if ($result !== null) {
        $separator = str_contains($redirectTo, '?') ? '&' : '?';
        $redirectTo .= $separator . ($result['success'] ? 'msg=' : 'error=') . urlencode($result['message']);
    }

    header('Location: ' . $redirectTo);
    exit;
}

$message = null;
$messageType = 'success';
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $messageType = 'danger';
}

$items = Cart::getItems();
$total = Cart::getTotalAmount();

require __DIR__ . '/partials/header.php';
?>
<section class="container py-5">
    <h1 class="h3 fw-bold mb-4">Giỏ hàng của bạn</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <!-- Trạng thái giỏ hàng trống -->
        <div class="text-center py-5">
            <p class="fs-1 mb-3">🛒</p>
            <p class="fs-5 text-secondary mb-4">Giỏ hàng của bạn đang trống.</p>
            <a href="<?= $basePath ?>/index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>

        <!-- Bảng giỏ hàng: hiển thị từ màn hình md trở lên -->
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-center" style="width:170px;">Số lượng</th>
                        <th class="text-end">Thành tiền</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img
                                    src="<?= $item['image_path']
                                        ? htmlspecialchars($basePath . '/uploads/' . $item['image_path'], ENT_QUOTES, 'UTF-8')
                                        : $basePath . '/assets/img/tech-placeholder.svg' ?>"
                                    class="rounded border cart-thumb"
                                    alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="fw-medium"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </td>
                        <td class="text-end"><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <form method="post" action="cart.php">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <button type="submit" name="action" value="decrease"
                                            class="btn btn-outline-secondary btn-sm qty-btn"
                                            aria-label="Giảm số lượng">−</button>
                                </form>
                                <span class="px-2 fw-semibold"><?= $item['quantity'] ?></span>
                                <form method="post" action="cart.php">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <button type="submit" name="action" value="increase"
                                            class="btn btn-outline-secondary btn-sm qty-btn"
                                            aria-label="Tăng số lượng"
                                            <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                                </form>
                            </div>
                            <?php if ($item['quantity'] >= $item['stock']): ?>
                                <p class="text-center text-muted small mb-0 mt-1">Đã đạt tồn kho tối đa</p>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold text-danger"><?= number_format($item['subtotal'], 0, ',', '.') ?> đ</td>
                        <td class="text-end">
                            <form method="post" action="cart.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" name="action" value="remove" class="btn btn-outline-danger btn-sm">Xoá</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Danh sách dạng thẻ: hiển thị trên màn hình nhỏ -->
        <div class="d-md-none">
            <?php foreach ($items as $item): ?>
                <div class="card mb-3 cart-item-card">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <img
                                src="<?= $item['image_path']
                                    ? htmlspecialchars($basePath . '/uploads/' . $item['image_path'], ENT_QUOTES, 'UTF-8')
                                    : $basePath . '/assets/img/tech-placeholder.svg' ?>"
                                class="rounded border cart-thumb flex-shrink-0"
                                alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="flex-grow-1">
                                <p class="fw-medium mb-1"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-secondary small mb-2"><?= number_format($item['price'], 0, ',', '.') ?> đ / sản phẩm</p>

                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <form method="post" action="cart.php">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" name="action" value="decrease"
                                                    class="btn btn-outline-secondary btn-sm qty-btn"
                                                    aria-label="Giảm số lượng">−</button>
                                        </form>
                                        <span class="px-2 fw-semibold"><?= $item['quantity'] ?></span>
                                        <form method="post" action="cart.php">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" name="action" value="increase"
                                                    class="btn btn-outline-secondary btn-sm qty-btn"
                                                    aria-label="Tăng số lượng"
                                                    <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                                        </form>
                                    </div>
                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="submit" name="action" value="remove" class="btn btn-outline-danger btn-sm">Xoá</button>
                                    </form>
                                </div>

                                <p class="text-end fw-semibold text-danger mt-2 mb-0">
                                    <?= number_format($item['subtotal'], 0, ',', '.') ?> đ
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tổng kết giỏ hàng -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 gap-3 border-top pt-4">
            <form method="post" action="cart.php" onsubmit="return confirm('Xoá toàn bộ giỏ hàng?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" name="action" value="clear" class="btn btn-outline-secondary btn-sm">
                    Xoá toàn bộ giỏ hàng
                </button>
            </form>

            <div class="text-md-end">
                <p class="mb-1 text-secondary">Tổng tiền dự kiến</p>
                <p class="fs-3 fw-bold text-danger mb-2"><?= number_format($total, 0, ',', '.') ?> đ</p>
                <a href="../index.php?action=checkout" class="btn btn-primary">Tiến hành đặt hàng</a>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
