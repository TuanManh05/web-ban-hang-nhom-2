<?php

declare(strict_types=1);

/**
 * Được require từ OrderController::invoice() / adminInvoice().
 * Biến có sẵn: $order (array|null), $orderCode (string), $backUrl (string), $pageTitle (string)
 *
 * Trang này KHÔNG dùng partials/header.php và footer.php (không có menu, không
 * có thanh điều hướng) để đảm bảo bản in sạch sẽ, chỉ có nội dung hoá đơn.
 */
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Hóa đơn', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 16mm;
        }

        body {
            background: #f1f3f5;
        }

        .invoice-sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }

        @media print {
            body {
                background: #fff;
            }

            .d-print-none {
                display: none !important;
            }

            .invoice-sheet {
                box-shadow: none !important;
                border: none !important;
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="py-4">
<div class="container">
    <div class="d-print-none d-flex justify-content-between align-items-center mb-3"
         style="max-width: 800px; margin: 0 auto;">
        <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm">
            &laquo; Quay lại
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">🖨️ In hóa đơn</button>
    </div>

    <div class="invoice-sheet shadow-sm rounded p-4 p-md-5">
        <?php if (!$order): ?>
            <div class="alert alert-danger mb-0">
                Không tìm thấy đơn hàng, hoặc bạn không có quyền xem hoá đơn này.
            </div>
        <?php else: ?>
            <?php require __DIR__ . '/_invoice-body.php'; ?>
            <p class="text-center text-secondary small mt-5 mb-0">
                Cảm ơn quý khách đã mua hàng tại Nhóm 6 Tech Store!
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>