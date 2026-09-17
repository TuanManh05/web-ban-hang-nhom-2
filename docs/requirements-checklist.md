# Đối chiếu yêu cầu bài tập nhóm

## Yêu cầu kỹ thuật

| Yêu cầu trong đề | Phần đáp ứng trong dự án | Trạng thái |
|---|---|---|
| PHP + MySQL ở Backend | PHP thuần, PDO và MariaDB/MySQL | Đạt |
| Trang người dùng và trang Admin | Trang chủ, sản phẩm, giỏ hàng, đơn hàng và dashboard quản trị | Đạt |
| Giao diện đẹp, hiện đại, responsive | Bootstrap 5 và CSS responsive riêng | Đạt |
| CRUD nội dung | CRUD danh mục, CRUD sản phẩm; đọc và cập nhật trạng thái đơn hàng | Đạt |
| Đăng nhập, đăng xuất, phân quyền | Session dùng chung qua `Security`, middleware chặn trang Admin | Đạt |
| Phân trang | Danh sách sản phẩm, lịch sử đơn hàng và quản lý đơn hàng | Đạt |
| Upload nhiều ảnh | Admin chọn tối đa 5 ảnh khi thêm/sửa sản phẩm | Đạt |
| Sử dụng editor | Trình soạn thảo định dạng mô tả sản phẩm, có lọc HTML an toàn | Đạt |

## Phần nâng cao bổ sung

- Giỏ hàng được lưu trong session.
- CSRF token cho các thao tác POST.
- Tự động hết hạn phiên đăng nhập sau 30 phút không hoạt động.
- Chart.js hiển thị doanh thu 7 ngày, số đơn theo trạng thái và top 5 sản phẩm bán chạy.
- Tìm kiếm, lọc danh mục, sắp xếp theo giá.
- Kiểm tra định dạng, dung lượng và số lượng ảnh tải lên.

## Phần cần chuẩn bị ngoài source code

- Slide báo cáo ngắn gọn, trực quan.
- Phân tích thiết kế hệ thống và sơ đồ database trong slide.
- Bảng phân công, mức độ tham gia và hoàn thành của từng thành viên.
- Source code cuối cùng và link deploy nếu nhóm có triển khai online.
