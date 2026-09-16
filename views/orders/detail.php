<?php declare(strict_types=1); require __DIR__ . '/../partials/header.php'; ?>
<section class="container py-5">
<?php if (!$order): ?><div class="alert alert-danger">Đơn hàng không tồn tại hoặc không thuộc tài khoản của bạn.</div>
<?php else: ?>
    <h1 class="h3 fw-bold">Đơn hàng DH<?= str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
    <?php $labels = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','completed'=>'Hoàn thành','cancelled'=>'Đã hủy']; ?>
    <p>Trạng thái: <strong><?= htmlspecialchars($labels[$order['status']] ?? (string) $order['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p>Người nhận: <?= htmlspecialchars((string) $order['customer_name'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $order['phone'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>Địa chỉ: <?= htmlspecialchars((string) $order['address'], ENT_QUOTES, 'UTF-8') ?></p>
    <div class="table-responsive"><table class="table"><thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead><tbody>
    <?php foreach ($order['items'] as $item): ?><tr><td><?= htmlspecialchars((string) $item['product_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format((float) $item['price'], 0, ',', '.') ?> đ</td><td><?= (int) $item['quantity'] ?></td><td><?= number_format((float) $item['price'] * (int) $item['quantity'], 0, ',', '.') ?> đ</td></tr><?php endforeach; ?>
    </tbody></table></div>
    <p class="text-end fs-5 fw-bold">Tổng cộng: <?= number_format((float) $order['total_amount'], 0, ',', '.') ?> đ</p>
    <?php if ($order['status'] === 'pending'): ?><form method="post" action="<?= $basePath ?>/index.php?action=order-cancel" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><button class="btn btn-danger">Hủy đơn hàng</button></form><?php endif; ?>
    <a href="<?= $basePath ?>/index.php?action=order-invoice&id=<?= (int) $order['id'] ?>" class="btn btn-outline-primary" target="_blank">🖨️ Xem/In hoá đơn</a>
<?php endif; ?>
<a href="<?= $basePath ?>/index.php?action=orders" class="btn btn-outline-secondary">Quay lại</a>
</section><?php require __DIR__ . '/../partials/footer.php'; ?>