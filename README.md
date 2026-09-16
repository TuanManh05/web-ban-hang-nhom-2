# Website bán hàng cơ bản - Nhóm 2

Bài tập nhóm cuối kỳ môn Lập trình Web, xây dựng bằng PHP thuần, MariaDB/MySQL, Bootstrap và Chart.js.

## Công nghệ

- PHP 8.2 (XAMPP 8.2.12)
- MariaDB/MySQL và phpMyAdmin
- HTML, CSS, JavaScript, Bootstrap 5
- Chart.js cho trang thống kê quản trị

## Cài đặt trên Windows

1. Cài XAMPP vào `C:\xampp`.
2. Clone repository vào `C:\xampp\htdocs\web-ban-hang-nhom-6`.
3. Mở XAMPP Control Panel, khởi động Apache và MySQL.
4. Truy cập `http://localhost/phpmyadmin`.
5. Chọn **Import** và nhập file `database/database.sql`.
6. Mở `http://localhost/web-ban-hang-nhom-6`.

Tài khoản quản trị mẫu sau khi import database:

- Email: `admin@nhom2.local`
- Mật khẩu: `Admin@123`

Có thể tạo lại toàn bộ database cùng dữ liệu mẫu cho SHOP-17 bằng lệnh:

```bash
php artisan migrate:fresh --seed
```

Lệnh tạo một admin, một customer, các danh mục mẫu và 21 sản phẩm mẫu có gallery ba ảnh.

Thông tin kết nối mặc định dành cho XAMPP nằm trong `config/database.php`. Nếu máy dùng cổng hoặc tài khoản khác, sao chép `config/database.local.example.php` thành `config/database.local.php` rồi chỉnh lại. File cục bộ này không được commit lên GitHub.

## Quy trình Git

- `main`: phiên bản ổn định để nộp bài.
- `develop`: tích hợp công việc của nhóm.
- Mỗi task dùng nhánh riêng, ví dụ `feature/SHOP-3-authentication`.
- Tạo Pull Request vào `develop`; không push chức năng trực tiếp vào `main`.

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
