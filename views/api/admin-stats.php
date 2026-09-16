<?php

declare(strict_types=1);

require_once __DIR__ . '/../../middleware/Security.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

Security::startSession();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập thống kê.']);
    exit;
}

try {
    require_once __DIR__ . '/../../config/database.php';
    $pdo = database();

    $revenueStmt = $pdo->query(
        "SELECT DATE(created_at) AS day, SUM(total_amount) AS revenue
         FROM orders
         WHERE status = 'completed' AND created_at >= (CURRENT_DATE - INTERVAL 6 DAY)
         GROUP BY DATE(created_at)
         ORDER BY day ASC"
    );
    $revenueRows = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);

    $revenueByDay = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-{$i} day"));
        $revenueByDay[$day] = 0.0;
    }
    foreach ($revenueRows as $row) {
        $revenueByDay[$row['day']] = (float) $row['revenue'];
    }

    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã huỷ',
    ];
    $statusStmt = $pdo->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
    $statusCounts = array_fill_keys(array_keys($statusLabels), 0);
    foreach ($statusStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($statusCounts[$row['status']])) {
            $statusCounts[$row['status']] = (int) $row['total'];
        }
    }

    $topProductsStmt = $pdo->query(
        "SELECT p.name, SUM(oi.quantity) AS total_sold
         FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         INNER JOIN products p ON p.id = oi.product_id
         WHERE o.status = 'completed'
         GROUP BY p.id, p.name
         ORDER BY total_sold DESC
         LIMIT 5"
    );
    $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $totalRevenue = (float) $pdo->query(
        "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'"
    )->fetchColumn();

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ],
        'revenue_by_day' => [
            'labels' => array_keys($revenueByDay),
            'values' => array_values($revenueByDay),
        ],
        'orders_by_status' => [
            'labels' => array_values($statusLabels),
            'values' => array_values($statusCounts),
        ],
        'top_products' => [
            'labels' => array_column($topProducts, 'name'),
            'values' => array_map('intval', array_column($topProducts, 'total_sold')),
        ],
    ]);
} catch (Throwable $e) {
    error_log($e->__toString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể tải dữ liệu thống kê lúc này.']);
}
