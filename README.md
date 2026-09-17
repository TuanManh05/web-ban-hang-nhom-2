# Website bán hàng công nghệ - Nhóm 2

Bài tập nhóm cuối kỳ môn Lập trình Web, xây dựng bằng PHP thuần, MariaDB/MySQL, Bootstrap và Chart.js.

## Công nghệ

- PHP 8.2 (XAMPP 8.2.12)
- MariaDB/MySQL và phpMyAdmin
- HTML, CSS, JavaScript, Bootstrap 5
- Chart.js cho trang thống kê quản trị

## Chức năng theo yêu cầu đề bài

- Trang khách hàng và trang quản trị riêng, có phân quyền bằng session.
- Đăng ký, đăng nhập, đăng xuất, hồ sơ tài khoản và giỏ hàng lưu trong session.
- CRUD danh mục, sản phẩm và quản lý trạng thái đơn hàng.
- Tìm kiếm, lọc, sắp xếp và phân trang sản phẩm/đơn hàng.
- Upload đồng thời tối đa 5 ảnh cho một sản phẩm; hỗ trợ JPG, PNG và WEBP.
- Trình soạn thảo có định dạng cho mô tả sản phẩm.
- Dashboard dùng Chart.js hiển thị doanh thu 7 ngày, trạng thái đơn và sản phẩm bán chạy.
- Giao diện Bootstrap responsive trên máy tính và điện thoại.

Xem bảng đối chiếu chi tiết tại [`docs/requirements-checklist.md`](docs/requirements-checklist.md).

## Cài đặt trên Windows

1. Cài XAMPP vào `C:\xampp`.
2. Clone repository vào `C:\xampp\htdocs\web-ban-hang-nhom-6`.
3. Mở XAMPP Control Panel, khởi động Apache và MySQL.
4. Truy cập `http://localhost/phpmyadmin`.
5. Chọn **Import** và nhập file `database/database.sql`. File này tạo sẵn 2 tài khoản mẫu, 6 danh mục, 21 sản phẩm và 63 bản ghi ảnh.
6. Mở `http://localhost/web-ban-hang-nhom-6`.

Tài khoản mẫu sau khi import database:

- Quản trị viên: `admin@nhom2.local` / `Admin@123`
- Khách hàng: `customer@nhom2.local` / `Customer@123`

Có thể tạo lại toàn bộ database cùng dữ liệu mẫu cho SHOP-17 bằng lệnh:

```bash
php artisan migrate:fresh --seed
```

Đây là script dòng lệnh riêng của dự án PHP thuần, không phải Laravel Artisan. Lệnh xóa dữ liệu hiện có rồi tạo lại một admin, một customer, các danh mục mẫu và 21 sản phẩm mẫu có gallery ba ảnh.

Thông tin kết nối mặc định dành cho XAMPP nằm trong `config/database.php`. Nếu máy dùng cổng hoặc tài khoản khác, sao chép `config/database.local.example.php` thành `config/database.local.php` rồi chỉnh lại. File cục bộ này không được commit lên GitHub.

## Quy trình Git

- `main`: phiên bản ổn định để nộp bài.
- Mỗi task dùng nhánh riêng, ví dụ `feature/SHOP-3-authentication`.
- Tạo Pull Request vào main khi được 1 người review code.

Quy ước commit: `feat`, `fix`, `ui`, `docs`, `test`, `chore`.

## Cấu trúc thư mục

```text
assets/       CSS, JavaScript và hình ảnh giao diện
config/       cấu hình ứng dụng và kết nối PDO
controllers/  xử lý yêu cầu
database/     lược đồ và dữ liệu mẫu SQL
models/       thao tác dữ liệu
uploads/      ảnh do người dùng tải lên
views/        giao diện PHP
```

Xem [sơ đồ ERD](docs/database-erd.md) để biết quan hệ giữa các bảng.
