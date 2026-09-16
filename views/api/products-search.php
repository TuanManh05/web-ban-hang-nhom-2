<?php
declare(strict_types=1);

/**
 * API gợi ý sản phẩm cho ô tìm kiếm trên header.
 *
 * GET /views/api/products-search.php?q=<từ khoá>
 *
 * - Chỉ trả kết quả khi từ khoá có tối thiểu 2 ký tự (chặn cả phía server,
 *   không chỉ dựa vào JS phía client).
 * - Luôn trả JSON hợp lệ (kể cả khi lỗi), để JS phía client không bao giờ
 *   nhận về HTML/exception làm hỏng thanh tìm kiếm.
 */

header('Content-Type: application/json; charset=utf-8');
// Endpoint chỉ đọc dữ liệu công khai (sản phẩm đang bán), không cần xác thực,
// nhưng vẫn giới hạn phương thức để tránh lạm dụng.
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

// Tính lại basePath giống header.php vì file này có thể được gọi trực tiếp,
// không thông qua index.php.
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__ . '/../..');
$basePath = '';
if ($documentRoot && $projectRoot && str_starts_with($projectRoot, $documentRoot)) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

$keyword = trim((string) ($_GET['q'] ?? ''));

// Điều kiện hoàn thành: chỉ gọi/trả kết quả khi nhập tối thiểu 2 ký tự.
if (mb_strlen($keyword) < 2) {
    echo json_encode(['success' => true, 'query' => $keyword, 'data' => []]);
    exit;
}

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/ProductModel.php';

    $pdo = database();
    $productModel = new ProductModel($pdo);

    $results = $productModel->searchProducts([
        'q' => $keyword,
        'limit' => 6,
        'offset' => 0,
    ]);

    $data = array_map(static function (array $product) use ($basePath): array {
        $imagePath = !empty($product['image_path'])
            ? $basePath . '/uploads/' . $product['image_path']
            : $basePath . '/assets/img/tech-placeholder.svg';

        return [
            'id' => (int) $product['id'],
            'name' => (string) $product['name'],
            'price' => (float) $product['price'],
            'price_formatted' => number_format((float) $product['price'], 0, ',', '.') . '₫',
            'image' => $imagePath,
            'url' => $basePath . '/views/product-detail.php?id=' . (int) $product['id'],
        ];
    }, $results);

    echo json_encode(['success' => true, 'query' => $keyword, 'data' => $data]);
} catch (Throwable $e) {
    // Không để lộ chi tiết lỗi hệ thống ra ngoài; JS phía client chỉ cần biết
    // là gọi API thất bại để hiển thị thông báo phù hợp mà không phá vỡ ô tìm kiếm.
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể tải gợi ý sản phẩm lúc này.']);
}
