# Checklist kiểm thử Sprint 4 (SHOP-40)

Người kiểm thử: Phạm Đình Khoa
Môi trường: XAMPP local (PHP 8.2, MySQL), tài khoản admin@nhom6.local / customer@nhom6.local

**Hướng dẫn điền:** với mỗi ca, thực hiện đúng bước test, ghi kết quả thực tế thấy được,
rồi tự đối chiếu với cột "Kết quả mong đợi" để kết luận Pass/Fail. Nếu Fail, tạo ngay
1 Jira Bug link tới dòng tương ứng (điền mã bug vào cột "Bug liên quan").

## A. Product API (SHOP-36/37 — views/api/products-search.php)

| Mã | Ca kiểm thử | Bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Bug liên quan |
|---|---|---|---|---|---|---|
| A1 | Tìm từ khoá hợp lệ | Mở `views/api/products-search.php?q=laptop` | Trả JSON `200`, có mảng sản phẩm khớp từ khoá | | | |
| A2 | Từ khoá quá ngắn | Mở `...products-search.php?q=a` | Trả JSON hợp lệ, mảng rỗng (không phải lỗi 500) | | | |
| A3 | Không truyền `q` | Mở `...products-search.php` (không có `?q=`) | Trả JSON hợp lệ, không crash | | | |
| A4 | Ký tự đặc biệt / SQL injection thử | Mở `...products-search.php?q=' OR '1'='1` | Trả JSON rỗng hoặc không khớp, KHÔNG lộ lỗi SQL, KHÔNG trả toàn bộ sản phẩm | | | |
| A5 | Gửi sai method | Gọi bằng POST thay vì GET (dùng Postman/curl) | Trả lỗi rõ ràng (405 hoặc JSON `success:false`), không crash trắng trang | | | |

## B. API thống kê (SHOP-40 — views/api/admin-stats.php)

| Mã | Ca kiểm thử | Bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Bug liên quan |
|---|---|---|---|---|---|---|
| B1 | Gọi khi chưa đăng nhập | Đăng xuất, mở trực tiếp `index.php?action=admin-stats` | Chuyển hướng về trang đăng nhập, không lộ dữ liệu thống kê | | | |
| B2 | Gọi bằng tài khoản khách (không phải admin) | Đăng nhập `customer@nhom6.local`, mở `index.php?action=admin-stats` | Bị chặn (không vào được trang thống kê) | | | |
| B3 | Gọi thẳng file API bằng tài khoản khách | Đăng nhập khách, mở trực tiếp `views/api/admin-stats.php` | Trả JSON `success:false`, mã lỗi 401/403, không lộ dữ liệu | | | |
| B4 | Admin xem thống kê khi **CÓ** đơn hàng | Đăng nhập admin, đặt trước 1-2 đơn hàng, vào trang Thống kê | 3 biểu đồ hiện đúng số liệu khớp với đơn hàng đã tạo | | | |
| B5 | Admin xem thống kê khi **KHÔNG** có đơn hàng | Xoá hết đơn hàng test (hoặc dùng DB mới seed), vào trang Thống kê | Biểu đồ vẫn hiện ra bình thường (giá trị 0 / rỗng), KHÔNG lỗi trắng trang, có thông báo "chưa có dữ liệu" ở phần top sản phẩm | | | |
| B6 | Gửi sai method vào API thống kê | Gọi `views/api/admin-stats.php` bằng POST | Trả lỗi 405, không crash | | | |

## C. Session — đăng nhập, giỏ hàng, hết hạn (SHOP-32)

| Mã | Ca kiểm thử | Bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Bug liên quan |
|---|---|---|---|---|---|---|
| C1 | Đăng nhập duy trì khi chuyển trang | Đăng nhập xong, bấm qua lại nhiều trang khác nhau | Vẫn giữ trạng thái đăng nhập, không bị văng ra | | | |
| C2 | Đăng xuất xoá phiên | Đăng xuất, sau đó bấm nút Back trên trình duyệt | Không thể xem lại trang cần đăng nhập, bị đá về trang đăng nhập | | | |
| C3 | Giỏ hàng lưu đúng theo session | Thêm sản phẩm vào giỏ, mở tab ẩn danh khác | Tab ẩn danh không thấy giỏ hàng của tab kia (giỏ hàng riêng theo session) | | | |
| C4 | Session hết hạn do không hoạt động | Trong `middleware/Security.php`, tạm đổi `INACTIVITY_TIMEOUT` từ 1800 xuống 10 (giây) để test nhanh, đăng nhập, chờ 15 giây, bấm 1 link bất kỳ | Tự động bị đăng xuất, chuyển về trang đăng nhập kèm thông báo hết hạn. **Nhớ đổi lại 1800 sau khi test xong.** | | | |
| C5 | Không lỗi gọi session_start() nhiều lần | Mở bất kỳ trang nào, xem có dòng lỗi PHP "session already started" không (bật tạm display_errors nếu cần) | Không xuất hiện lỗi này ở bất kỳ trang nào | | | |

## D. Phân quyền API và Admin (SHOP-32/33)

| Mã | Ca kiểm thử | Bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Bug liên quan |
|---|---|---|---|---|---|---|
| D1 | Khách cố truy cập trang admin | Đăng nhập khách, gõ thẳng `index.php?action=admin` | Bị chặn, chuyển hướng về trang chủ hoặc đăng nhập | | | |
| D2 | Khách cố thêm sản phẩm qua URL | Đăng nhập khách, gõ thẳng `index.php?action=product-create` | Bị chặn | | | |
| D3 | Chưa đăng nhập cố vào giỏ hàng/đặt hàng | Đăng xuất, gõ thẳng `index.php?action=checkout` | Bị chặn hoặc yêu cầu đăng nhập trước | | | |
| D4 | CSRF token sai/thiếu bị từ chối | Dùng Postman gửi POST tới `index.php?action=product-store` không kèm `csrf_token` | Bị từ chối (mã 419), không thêm được sản phẩm | | | |
| D5 | Token hợp lệ không làm hỏng chức năng | Thêm/sửa/xoá sản phẩm và danh mục bình thường qua giao diện web | Tất cả thao tác chạy đúng, không bị chặn nhầm | | | |

## E. Không hiển thị lỗi PHP chi tiết (SHOP-33)

| Mã | Ca kiểm thử | Bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Pass/Fail | Bug liên quan |
|---|---|---|---|---|---|---|
| E1 | Gây lỗi cố ý (VD: sửa sai tên cột DB tạm thời, hoặc truyền id âm/chữ vào `?id=abc`) | Mở `views/product-detail.php?id=abc` hoặc tương tự | Hiện trang lỗi thân thiện ("Đã có lỗi xảy ra"), KHÔNG hiện dòng lỗi PHP/SQL chi tiết, KHÔNG lộ đường dẫn file server | | | |

---

**Kết luận chung:** ______________________________
(Không đánh dấu Done task SHOP-40 nếu còn bug ở mức Fail nghiêm trọng — đặc biệt các mục B1-B3, D1-D4 vì liên quan bảo mật/phân quyền.)
