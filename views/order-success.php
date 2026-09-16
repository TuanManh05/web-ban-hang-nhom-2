<?php

declare(strict_types=1);

/**
 * Được require từ OrderController::store() sau khi tạo đơn thành công.
 * Biến có sẵn: $orderCode (string), $order (array|null - dữ liệu đơn hàng đầy đủ kèm items)
 */
?>
<?php require __DIR__ . '/partials/header.php'; ?>
<section class="container py-5">
    <div class="d-print-none text-center mb-4">
        <div class="display-5 mb-2" aria-hidden="true">✓</div>
        <h1 class="h4 fw-bold mb-1">Đặt hàng thành công!</h1>
        <p class="text-secondary mb-0">Vui lòng lưu lại mã đơn hàng bên dưới để tra cứu sau này.</p>
    </div>

    <div class="card border-0 shadow-sm mx-auto invoice-print-area" style="max-width: 800px;">
        <div class="card-body p-4 p-md-5">
            <?php if ($order): ?>
                <?php require __DIR__ . '/orders/_invoice-body.php'; ?>
            <?php else: ?>
                <p class="text-secondary mb-2 text-center">Mã đơn hàng của bạn là:</p>
                <p class="fs-3 fw-bold text-primary text-center mb-0">
                    <?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-print-none d-flex justify-content-center gap-2 mt-4">
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">🖨️ In hóa đơn</button>
        <a href="<?= $basePath ?>/index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
</section>

<style>
    /* Trang in không hiển thị menu, footer và các nút thao tác */
    @media print {
        .store-header,
        .service-strip,
        .store-footer,
        .d-print-none {
            display: none !important;
        }

        .invoice-print-area {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
<?php require __DIR__ . '/partials/footer.php'; ?>