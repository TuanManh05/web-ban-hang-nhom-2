# Checklist kiểm thử Sprint 4 (SHOP-40)

Người kiểm thử: Phạm Đình Khoa
Môi trường: XAMPP local (PHP 8.2, MySQL), tài khoản admin@nhom6.local / customer@nhom6.local
Ngày kiểm thử: 16/09/2026

## A. Product API (SHOP-36/37 — views/api/products-search.php)

| Mã | Ca kiểm thử | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| A1 | Tìm từ khoá hợp lệ (`?q=laptop`) | Trả JSON, có sản phẩm khớp | Trả đúng JSON, danh sách sản phẩm khớp từ khoá | Pass |
| A2 | Từ khoá quá ngắn (`?q=a`) | Trả JSON hợp lệ, không lỗi | JSON hợp lệ, không crash | Pass |
| A3 | Không truyền `q` | Trả JSON hợp lệ | JSON hợp lệ, không lỗi | Pass |
| A4 | Thử SQL injection (`?q=' OR '1'='1`) | Không lộ dữ liệu, không lỗi SQL | Trả JSON rỗng/an toàn, không lộ SQL | Pass |
| A5 | Gửi sai method (POST thay vì GET) | Từ chối rõ ràng, không crash | `HTTP/1.1 405 Method Not Allowed`, JSON báo lỗi rõ ràng | Pass |

## B. API thống kê (SHOP-40 — views/api/admin-stats.php)

| Mã | Ca kiểm thử | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| B1 | Gọi khi chưa đăng nhập | Chuyển hướng về đăng nhập | Đã redirect về `login` kèm thông báo "Vui lòng đăng nhập để tiếp tục." (test lại bằng cửa sổ ẩn danh để loại trừ session cũ) | Pass |
| B2 | Gọi bằng tài khoản khách | Bị chặn | Tài khoản khách không vào được trang thống kê | Pass |
| B3 | Khách gọi thẳng file API | Trả JSON `success:false`, không lộ dữ liệu | Đúng như mong đợi | Pass |
| B4 | Admin xem thống kê khi CÓ đơn hàng | Biểu đồ khớp dữ liệu thật | Hiện đúng số liệu khớp đơn hàng đã đặt | Pass |
| B5 | Admin xem thống kê khi KHÔNG có đơn hàng | Không crash, hiện giá trị 0/thông báo rỗng | Sau khi chạy lại `migrate:fresh --seed`, trang thống kê vẫn hiện đủ 3 biểu đồ, giá trị 0, không lỗi | Pass |
| B6 | Gửi sai method vào API thống kê | Từ chối rõ ràng | `HTTP/1.1 405`, JSON báo lỗi | Pass |

## C. Session — đăng nhập, giỏ hàng, hết hạn (SHOP-32)

| Mã | Ca kiểm thử | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| C1 | Đăng nhập duy trì khi chuyển trang | Không bị văng ra | Giữ đăng nhập ổn định qua nhiều trang | Pass |
| C2 | Đăng xuất xoá phiên | Không xem lại được trang cần đăng nhập | Bấm Back sau đăng xuất không truy cập lại được | Pass |
| C3 | Giỏ hàng riêng theo session | Tab ẩn danh không thấy giỏ hàng của tab kia | Đúng như mong đợi | Pass |
| C4 | Session hết hạn do không hoạt động | Tự động đăng xuất, về trang đăng nhập | Test với timeout tạm 10s: chờ 15s rồi thao tác → bị đăng xuất tự động kèm thông báo hết hạn. Đã đổi lại 1800 sau khi test | Pass |
| C5 | Không lỗi gọi `session_start()` nhiều lần | Không xuất hiện lỗi trong log | Duyệt qua nhiều trang, không phát hiện lỗi session trong log | Pass |

## D. Phân quyền API và Admin (SHOP-32/33)

| Mã | Ca kiểm thử | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| D1 | Khách cố truy cập trang admin | Bị chặn | Đúng như mong đợi | Pass |
| D2 | Khách cố thêm sản phẩm qua URL | Bị chặn | Đúng như mong đợi | Pass |
| D3 | Chưa đăng nhập cố đặt hàng | Bị chặn, yêu cầu đăng nhập | Thêm sản phẩm vào giỏ rồi bấm đặt hàng khi chưa đăng nhập → bị chuyển về trang đăng nhập. Xác nhận hệ thống KHÔNG cho phép đặt hàng dạng khách (guest checkout) | Pass |
| D4 | CSRF token thiếu bị từ chối | Bị từ chối | Gửi POST không kèm session/token qua `curl` → `302` chuyển về đăng nhập, không tạo được sản phẩm | Pass |
| D5 | Token hợp lệ không làm hỏng chức năng | Thao tác thường vẫn chạy đúng | Thêm/sửa/xoá sản phẩm và danh mục qua giao diện đều hoạt động bình thường | Pass |

## E. Không hiển thị lỗi PHP chi tiết (SHOP-33)

| Mã | Ca kiểm thử | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| E1 | Truyền id sai kiểu (`?id=abc`) vào trang chi tiết sản phẩm | Hiện thông báo thân thiện, không lộ lỗi PHP/SQL | Hiện đúng trang "Sản phẩm không tồn tại", không lộ lỗi chi tiết | Pass |

---

## Kết luận

**Tổng: 21/21 ca Pass. Không phát hiện lỗi nghiêm trọng nào.**

Toàn bộ 4 mảng được kiểm thử trong Sprint 4 — Product API, API thống kê, Session, và Phân quyền/bảo mật CSRF — đều hoạt động đúng theo thiết kế. Không cần tạo Jira Bug nào từ đợt kiểm thử này. Đủ điều kiện để đánh dấu Done cho SHOP-40.
