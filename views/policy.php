<?php
declare(strict_types=1);

$pageTitle = 'Chính sách mua hàng';
require __DIR__ . '/partials/header.php';
?>

<section class="policy-hero">
    <div class="container">
        <p class="policy-breadcrumb"><a href="<?= $basePath ?>/index.php">Trang chủ</a> / Chính sách mua hàng</p>
        <span class="policy-kicker">MUA SẮM AN TÂM</span>
        <h1>Chính sách mua hàng</h1>
        <p>Thông tin về đặt hàng, thanh toán, giao nhận, đổi trả và bảo hành tại Nhóm 2 Tech Store.</p>
    </div>
</section>

<section class="container policy-page">
    <div class="policy-highlights">
        <article><span>✓</span><strong>Sản phẩm rõ ràng</strong><small>Giá bán và tồn kho được hiển thị trước khi đặt hàng.</small></article>
        <article><span>▣</span><strong>Thanh toán minh bạch</strong><small>Khách hàng kiểm tra đầy đủ tổng tiền trước khi xác nhận.</small></article>
        <article><span>🚚</span><strong>Giao hàng an toàn</strong><small>Đơn hàng được đóng gói và cập nhật trạng thái rõ ràng.</small></article>
        <article><span>↻</span><strong>Hỗ trợ đổi trả</strong><small>Tiếp nhận yêu cầu trong vòng 7 ngày khi đủ điều kiện.</small></article>
    </div>

    <div class="policy-layout">
        <div class="policy-content">
            <article class="policy-section" id="dat-hang">
                <span class="policy-number">01</span>
                <div>
                    <h2>Đặt hàng</h2>
                    <ul>
                        <li>Chọn sản phẩm, số lượng và thêm vào giỏ hàng.</li>
                        <li>Kiểm tra thông tin sản phẩm, giá bán và tổng tiền trước khi thanh toán.</li>
                        <li>Cung cấp đúng họ tên, số điện thoại và địa chỉ nhận hàng.</li>
                        <li>Mã đơn hàng được hiển thị ngay sau khi đặt hàng thành công.</li>
                    </ul>
                </div>
            </article>

            <article class="policy-section" id="thanh-toan">
                <span class="policy-number">02</span>
                <div>
                    <h2>Thanh toán</h2>
                    <ul>
                        <li>Khách hàng thanh toán theo phương thức được hiển thị tại trang thanh toán.</li>
                        <li>Giá sản phẩm tại thời điểm đặt hàng được lưu trong chi tiết đơn và không tự thay đổi.</li>
                        <li>Nhóm 2 Tech Store không yêu cầu cung cấp mật khẩu hoặc mã xác thực tài khoản.</li>
                    </ul>
                </div>
            </article>

            <article class="policy-section" id="giao-hang">
                <span class="policy-number">03</span>
                <div>
                    <h2>Giao nhận</h2>
                    <ul>
                        <li>Đơn hàng được xử lý sau khi cửa hàng xác nhận thông tin.</li>
                        <li>Thời gian giao hàng phụ thuộc địa chỉ nhận và tình trạng vận chuyển thực tế.</li>
                        <li>Khách hàng nên kiểm tra ngoại quan, số lượng và đúng sản phẩm khi nhận hàng.</li>
                        <li>Nếu kiện hàng có dấu hiệu hư hỏng, vui lòng liên hệ cửa hàng để được hỗ trợ.</li>
                    </ul>
                </div>
            </article>

            <article class="policy-section" id="doi-tra">
                <span class="policy-number">04</span>
                <div>
                    <h2>Đổi trả và bảo hành</h2>
                    <ul>
                        <li>Yêu cầu đổi trả được tiếp nhận trong vòng 7 ngày kể từ ngày nhận hàng.</li>
                        <li>Sản phẩm cần còn đầy đủ phụ kiện, bao bì và không hư hỏng do người sử dụng.</li>
                        <li>Sản phẩm lỗi kỹ thuật được kiểm tra trước khi đổi, sửa chữa hoặc bảo hành.</li>
                        <li>Không áp dụng đổi trả với sản phẩm đã bị can thiệp, rơi vỡ hoặc vào nước.</li>
                    </ul>
                </div>
            </article>
        </div>

        <aside class="policy-contact">
            <span>CẦN HỖ TRỢ?</span>
            <h2>Liên hệ cửa hàng</h2>
            <p>Đội ngũ hỗ trợ sẵn sàng giải đáp về đơn hàng và chính sách mua hàng.</p>
            <a href="tel:19006868"><strong>1900 6868</strong><small>8:00 – 21:00 mỗi ngày</small></a>
            <a href="mailto:nhom2@example.com"><strong>nhom2@example.com</strong><small>Phản hồi qua email</small></a>
            <a class="policy-shopping-link" href="<?= $basePath ?>/views/products.php">Tiếp tục mua sắm →</a>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
