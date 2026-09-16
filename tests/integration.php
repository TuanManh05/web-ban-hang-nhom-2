<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/Order.php';
require __DIR__ . '/../models/User.php';
require __DIR__ . '/../models/Product.php';
require __DIR__ . '/../models/ProductModel.php';

$pdo = database();
$databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if (!str_ends_with($databaseName, '_test')) {
    throw new RuntimeException('Chỉ được chạy kiểm thử trên database có hậu tố _test.');
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "PASS: {$message}\n";
}

$customer = $pdo->query("SELECT * FROM users WHERE role = 'customer' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$product = $pdo->query('SELECT * FROM products WHERE status = 1 AND stock >= 2 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
check((bool) $customer, 'Có tài khoản customer mẫu');
check((bool) $product, 'Có sản phẩm đủ tồn kho');
check((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 15, 'Seeder tạo đủ 15 sản phẩm mẫu');
check((int) $pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn() === 45, 'Seeder gắn đủ 3 ảnh cho mỗi sản phẩm');
check((int) $pdo->query('SELECT COUNT(*) FROM (SELECT product_id FROM product_images GROUP BY product_id HAVING COUNT(*) = 3) galleries')->fetchColumn() === 15, 'Cả 15 sản phẩm đều có gallery 3 ảnh');
$galleryProduct = Product::findById((int) $product['id']);
check($galleryProduct !== null && count($galleryProduct['images']) === 3, 'Trang chi tiết lấy đủ gallery sản phẩm');
foreach ($galleryProduct['images'] as $image) {
    check(is_file(__DIR__ . '/../uploads/' . $image['image_path']), 'File ảnh sản phẩm tồn tại: ' . $image['image_path']);
}

$orders = new Order($pdo);
$stockBefore = (int) $product['stock'];
$customerData = ['name' => 'Khách kiểm thử', 'phone' => '0900000000', 'address' => 'Địa chỉ kiểm thử', 'note' => ''];
$cart = [['product_id' => (int) $product['id'], 'quantity' => 1]];
$orderId = $orders->createFromCart((int) $customer['id'], $customerData, $cart);
check($orderId > 0, 'Tạo đơn hàng thành công');
check((int) $pdo->query('SELECT stock FROM products WHERE id = ' . (int) $product['id'])->fetchColumn() === $stockBefore - 1, 'Tạo đơn đã trừ tồn kho');
check($orders->findForUser($orderId, (int) $customer['id']) !== null, 'Chủ đơn xem được chi tiết đơn');
check($orders->findForUser($orderId, 999999) === null, 'Tài khoản khác không xem được đơn');
check(!$orders->cancelForUser($orderId, 999999), 'Tài khoản khác không hủy được đơn');
check($orders->cancelForUser($orderId, (int) $customer['id']), 'Khách hủy được đơn pending của mình');
check((int) $pdo->query('SELECT stock FROM products WHERE id = ' . (int) $product['id'])->fetchColumn() === $stockBefore, 'Hủy đơn đã hoàn lại tồn kho');
check(!$orders->cancelForUser($orderId, (int) $customer['id']), 'Không thể hủy lại đơn đã hủy');

$orderId2 = $orders->createFromCart((int) $customer['id'], $customerData, $cart);
check($orders->updateStatus($orderId2, 'confirmed'), 'Admin chuyển pending sang confirmed');
check(!$orders->updateStatus($orderId2, 'completed'), 'Chặn chuyển confirmed thẳng sang completed');
check($orders->updateStatus($orderId2, 'shipping'), 'Admin chuyển confirmed sang shipping');
check($orders->updateStatus($orderId2, 'completed'), 'Admin chuyển shipping sang completed');
check(!$orders->cancelForUser($orderId2, (int) $customer['id']), 'Khách không hủy được đơn completed');

$stockBeforeAdminCancel = (int) $pdo->query('SELECT stock FROM products WHERE id = ' . (int) $product['id'])->fetchColumn();
$orderId3 = $orders->createFromCart((int) $customer['id'], ['name' => 'Khách lọc đơn', 'phone' => '0911222333', 'address' => 'Địa chỉ lọc đơn', 'note' => ''], $cart);
check($orders->updateStatus($orderId3, 'confirmed'), 'Admin xác nhận đơn trước khi hủy');
check($orders->updateStatus($orderId3, 'cancelled'), 'Admin hủy được đơn đã xác nhận');
check((int) $pdo->query('SELECT stock FROM products WHERE id = ' . (int) $product['id'])->fetchColumn() === $stockBeforeAdminCancel, 'Admin hủy đơn đã hoàn lại tồn kho');
check(!$orders->updateStatus($orderId3, 'confirmed'), 'Không thể đổi trạng thái đơn đã hủy');

$results = $orders->search(['q' => 'Khách kiểm thử', 'status' => '', 'sort' => ''], 10, 0);
check(count($results) >= 2, 'Tìm đơn theo tên khách hàng');
$results = $orders->search(['q' => 'DH' . str_pad((string) $orderId3, 6, '0', STR_PAD_LEFT), 'status' => '', 'sort' => ''], 10, 0);
check(count($results) === 1 && (int) $results[0]['id'] === $orderId3, 'Tìm chính xác theo mã đơn định dạng DH000001');
$results = $orders->search(['q' => '0911222333', 'status' => 'cancelled', 'sort' => ''], 10, 0);
check(count($results) === 1 && (int) $results[0]['id'] === $orderId3, 'Tìm theo số điện thoại kết hợp lọc trạng thái');
$results = $orders->search(['q' => 'DH000000', 'status' => '', 'sort' => ''], 10, 0);
check($results === [], 'Mã đơn không hợp lệ không trả về toàn bộ danh sách');
$oldest = $orders->search(['q' => '', 'status' => '', 'sort' => 'oldest'], 100, 0);
$newest = $orders->search(['q' => '', 'status' => '', 'sort' => ''], 100, 0);
check((int) $oldest[0]['id'] <= (int) $oldest[count($oldest) - 1]['id'], 'Sắp xếp đơn cũ nhất ổn định');
check((int) $newest[0]['id'] >= (int) $newest[count($newest) - 1]['id'], 'Sắp xếp đơn mới nhất ổn định');
$totalAsc = $orders->search(['q' => '', 'status' => '', 'sort' => 'total_asc'], 100, 0);
$totalDesc = $orders->search(['q' => '', 'status' => '', 'sort' => 'total_desc'], 100, 0);
check((float) $totalAsc[0]['total_amount'] <= (float) $totalAsc[count($totalAsc) - 1]['total_amount'], 'Sắp xếp tổng tiền tăng dần');
check((float) $totalDesc[0]['total_amount'] >= (float) $totalDesc[count($totalDesc) - 1]['total_amount'], 'Sắp xếp tổng tiền giảm dần');
check($orders->countSearch(['q' => '0911222333', 'status' => 'cancelled']) === 1, 'Đếm kết quả lọc đơn phục vụ phân trang Admin');
check($orders->countForUser((int) $customer['id']) >= 3, 'Đếm đơn phục vụ phân trang khách hàng');

$products = (new ProductModel($pdo))->searchProducts(['q' => (string) $product['name'], 'category_id' => null, 'sort' => 'price_asc', 'limit' => 12, 'offset' => 0]);
check($products !== [], 'Tìm kiếm và sắp xếp sản phẩm');

$users = new User($pdo);
$users->updateName((int) $customer['id'], 'Khách đã cập nhật');
check($users->findById((int) $customer['id'])['name'] === 'Khách đã cập nhật', 'Cập nhật tên tài khoản');
$users->updatePassword((int) $customer['id'], 'MatKhauMoi123');
check(password_verify('MatKhauMoi123', (string) $users->findById((int) $customer['id'])['password']), 'Đổi và mã hóa mật khẩu');

echo "INTEGRATION TESTS PASSED\n";
