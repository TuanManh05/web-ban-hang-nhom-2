<?php

declare(strict_types=1);

/**
 * Partial dùng chung để hiển thị nội dung hoá đơn.
 * Yêu cầu 2 biến đã được định nghĩa trước khi include:
 * - array $order  : dữ liệu đơn hàng kèm 'items' (lấy trực tiếp từ Order::find()/findForUser())
 * - string $orderCode : mã đơn dạng "DH000123"
 */

$statusLabels = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h5 fw-bold mb-1">HÓA ĐƠN BÁN HÀNG</h2>
        <p class="text-secondary mb-0 small">Nhóm 6 Tech Store</p>
    </div>
    <div class="text-md-end small">
        <p class="mb-1"><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (!empty($order['created_at'])): ?>
            <p class="mb-1">
                <strong>Ngày đặt:</strong>
                <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $order['created_at'])), ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        <p class="mb-0">
            <strong>Trạng thái:</strong>
            <?= htmlspecialchars($statusLabels[$order['status']] ?? (string) $order['status'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</div>

<div class="mb-4">
    <h3 class="h6 fw-bold">Thông tin khách hàng</h3>
    <p class="mb-1">Họ tên: <?= htmlspecialchars((string) $order['customer_name'], ENT_QUOTES, 'UTF-8') ?></p>
    <p class="mb-1">Điện thoại: <?= htmlspecialchars((string) $order['phone'], ENT_QUOTES, 'UTF-8') ?></p>
    <p class="mb-1">Địa chỉ nhận hàng: <?= htmlspecialchars((string) $order['address'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php if (!empty($order['note'])): ?>
        <p class="mb-0">Ghi chú: <?= htmlspecialchars((string) $order['note'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</div>

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:48px;">#</th>
                <th>Sản phẩm</th>
                <th class="text-end">Đơn giá</th>
                <th class="text-center" style="width:90px;">SL</th>
                <th class="text-end">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars((string) $item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= number_format((float) $item['price'], 0, ',', '.') ?> đ</td>
                    <td class="text-center"><?= (int) $item['quantity'] ?></td>
                    <td class="text-end">
                        <?= number_format((float) $item['price'] * (int) $item['quantity'], 0, ',', '.') ?> đ
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="text-end">
    <p class="fs-5 fw-bold mb-0">
        Tổng cộng:
        <span class="text-danger"><?= number_format((float) $order['total_amount'], 0, ',', '.') ?> đ</span>
    </p>
</div>