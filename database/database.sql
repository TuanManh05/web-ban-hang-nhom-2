CREATE DATABASE IF NOT EXISTS web_ban_hang_nhom_6
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE web_ban_hang_nhom_6;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tài khoản mẫu:
--   admin@nhom2.local / Admin@123
--   customer@nhom2.local / Customer@123
INSERT INTO users (name, email, password, role, status) VALUES
    ('Quản trị viên', 'admin@nhom2.local', '$2y$10$nQF1UlbmOMssNjyhcMGzU.ivwSKdywgQK1GPqaEGgCgRsUS1TS79y', 'admin', 1),
    ('Khách hàng mẫu', 'customer@nhom2.local', '$2y$10$dhoO.F0yaM2KJtn4PHzpRerTxuMgBR0HOeFaE2PXrxZ9Nb1ErJWse', 'customer', 1);

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(15,2) UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    note VARCHAR(500) NULL,
    total_amount DECIMAL(15,2) UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    price DECIMAL(15,2) UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
        REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (name, slug) VALUES
    ('Thời trang', 'thoi-trang'),
    ('Phụ kiện', 'phu-kien'),
    ('Điện thoại', 'dien-thoai'),
    ('Laptop', 'laptop'),
    ('Máy ảnh', 'may-anh'),
    ('PC Gaming', 'pc-gaming'),
    ('Linh kiện', 'linh-kien');

-- Dữ liệu sản phẩm có sẵn để chỉ cần import file SQL là chạy được website.
INSERT INTO products (id, category_id, name, slug, description, price, stock, status) VALUES
    (1, 4, 'Laptop Mỏng Nhẹ', 'laptop-van-phong-mong-nhe', 'Sản phẩm mẫu phục vụ phát triển: Laptop thiết kế gọn nhẹ, phù hợp học tập và công việc văn phòng hằng ngày.', 15990000, 18, 1),
    (2, 4, 'Laptop Lập Trình', 'laptop-lap-trinh-hieu-nang-cao', 'Sản phẩm mẫu phục vụ phát triển: Laptop hiệu năng cao dành cho lập trình, học tập và xử lý nhiều tác vụ.', 23990000, 12, 1),
    (3, 4, 'Laptop Học Tập 14"', 'laptop-hoc-tap-14-inch', 'Sản phẩm mẫu phục vụ phát triển: Laptop 14 inch nhỏ gọn, đáp ứng nhu cầu học trực tuyến và làm bài tập.', 12990000, 25, 1),
    (4, 4, 'Laptop 15"', 'laptop-man-hinh-lon-15-inch', 'Sản phẩm mẫu phục vụ phát triển: Laptop màn hình lớn, không gian hiển thị rộng và bàn phím thuận tiện khi làm việc.', 18990000, 15, 1),
    (5, 3, 'Điện Thoại Tràn Viền', 'dien-thoai-man-hinh-tran-vien', 'Sản phẩm mẫu phục vụ phát triển: Điện thoại thông minh với màn hình lớn, hiển thị rõ và thao tác cảm ứng thuận tiện.', 13990000, 20, 1),
    (6, 3, 'Điện Thoại Đen', 'dien-thoai-thong-minh-mau-den', 'Sản phẩm mẫu phục vụ phát triển: Điện thoại màu đen với thiết kế hiện đại, phù hợp nhu cầu liên lạc và giải trí.', 11990000, 22, 1),
    (7, 3, 'Điện Thoại Camera Kép', 'dien-thoai-thong-minh-camera-kep', 'Sản phẩm mẫu phục vụ phát triển: Điện thoại thông minh trang bị cụm camera kép và màn hình sắc nét.', 16990000, 14, 1),
    (8, 3, 'Điện Thoại Slim', 'dien-thoai-thiet-ke-mong-nhe', 'Sản phẩm mẫu phục vụ phát triển: Điện thoại có thiết kế mỏng nhẹ, dễ cầm nắm và mang theo hằng ngày.', 9990000, 28, 1),
    (9, 2, 'Tai Nghe Wireless', 'tai-nghe-chup-tai-khong-day', 'Sản phẩm mẫu phục vụ phát triển: Tai nghe chụp tai không dây, đệm tai êm và phù hợp nghe nhạc hằng ngày.', 1490000, 30, 1),
    (10, 2, 'Tai Nghe Chống Ồn', 'tai-nghe-chup-tai-chong-on', 'Sản phẩm mẫu phục vụ phát triển: Tai nghe chụp tai hỗ trợ giảm tiếng ồn, thích hợp làm việc và giải trí.', 2290000, 16, 1),
    (11, 2, 'Tai Nghe Studio', 'tai-nghe-chup-tai-studio', 'Sản phẩm mẫu phục vụ phát triển: Tai nghe chụp tai kiểu Studio với âm thanh rõ và thiết kế chắc chắn.', 1890000, 19, 1),
    (12, 5, 'Máy Ảnh Lens Rời', 'may-anh-ong-kinh-roi-mau-den', 'Sản phẩm mẫu phục vụ phát triển: Máy ảnh ống kính rời màu đen, phù hợp chụp ảnh chân dung và phong cảnh.', 18490000, 10, 1),
    (13, 5, 'Máy Ảnh Cơ Bản', 'may-anh-danh-cho-nguoi-moi', 'Sản phẩm mẫu phục vụ phát triển: Máy ảnh dễ sử dụng cho người mới bắt đầu học chụp ảnh.', 12990000, 13, 1),
    (14, 5, 'Máy Ảnh Du Lịch', 'may-anh-du-lich-nho-gon', 'Sản phẩm mẫu phục vụ phát triển: Máy ảnh nhỏ gọn, thuận tiện mang theo trong các chuyến đi.', 15490000, 11, 1),
    (15, 5, 'Máy Ảnh Pro', 'may-anh-chup-anh-chuyen-nghiep', 'Sản phẩm mẫu phục vụ phát triển: Máy ảnh hiệu năng cao dành cho nhu cầu chụp ảnh chuyên nghiệp.', 27990000, 8, 1),
    (16, 6, 'PC Gaming Yasuo', 'pc-gaming-yasuo', 'Sản phẩm mẫu phục vụ phát triển: Intel Core i5-12400F, RTX 3050 6GB, RAM 16GB và SSD 500GB; phù hợp chơi game Full HD.', 17247000, 9, 1),
    (17, 6, 'PC Gaming Storm C', 'pc-gaming-storm-c', 'Sản phẩm mẫu phục vụ phát triển: Intel Core i5-12400F, RTX 5060, RAM 16GB và SSD 512GB; đáp ứng tốt eSports và game AAA.', 25737000, 7, 1),
    (18, 6, 'PC Gaming Karmish', 'pc-gaming-karmish', 'Sản phẩm mẫu phục vụ phát triển: Intel Core i5-14400F, RTX 5060, RAM DDR5 16GB, SSD 512GB và Wi-Fi.', 30451000, 5, 1),
    (19, 2, 'Chuột Gaming RGB', 'chuot-gaming-rgb-co-day', 'Sản phẩm mẫu phục vụ phát triển: Chuột gaming có dây với đèn RGB, cảm biến chính xác và thiết kế thuận tay cho các phiên chơi dài.', 690000, 32, 1),
    (20, 2, 'Bàn Phím Cơ RGB', 'ban-phim-co-gaming-rgb', 'Sản phẩm mẫu phục vụ phát triển: Bàn phím cơ gaming có đèn RGB, phản hồi phím rõ và bố cục thuận tiện cho chơi game lẫn làm việc.', 1490000, 24, 1),
    (21, 2, 'Tay Cầm Không Dây', 'tay-cam-choi-game-khong-day', 'Sản phẩm mẫu phục vụ phát triển: Tay cầm chơi game không dây với hai cần analog, bố cục nút quen thuộc và kết nối ổn định.', 1290000, 20, 1),
    (22, 7, 'SSD Patriot P300 512GB', 'ssd-patriot-p300-512gb', 'Sản phẩm mẫu phục vụ phát triển: SSD M.2 2280 NVMe PCIe Gen3 x4, dung lượng 512GB, tốc độ đọc/ghi tối đa khoảng 1700/1200MB/s.', 760000, 18, 1),
    (23, 7, 'RAM SSTC 8GB DDR4 3200', 'ram-sstc-8gb-ddr4-3200', 'Sản phẩm mẫu phục vụ phát triển: RAM PC 8GB (1x8GB), chuẩn DDR4, bus 3200MHz, tương thích nền tảng Intel và AMD.', 1350000, 24, 1),
    (24, 7, 'RAM VSP 16GB DDR4 3200', 'ram-vsp-16gb-ddr4-3200', 'Sản phẩm mẫu phục vụ phát triển: RAM PC 16GB (1x16GB), chuẩn DDR4, bus 3200MHz, hỗ trợ Intel và AMD.', 2490000, 16, 1);

INSERT INTO product_images (product_id, image_path, is_primary) VALUES
    (1, 'product-laptop-01.jpg', 1), (1, 'product-laptop-02.jpg', 0), (1, 'product-laptop-03.jpg', 0),
    (2, 'product-laptop-04.jpg', 1), (2, 'product-laptop-01.jpg', 0), (2, 'product-laptop-02.jpg', 0),
    (3, 'product-laptop-01.jpg', 1), (3, 'product-laptop-02.jpg', 0), (3, 'product-laptop-03.jpg', 0),
    (4, 'product-laptop-03.jpg', 1), (4, 'product-laptop-01.jpg', 0), (4, 'product-laptop-02.jpg', 0),
    (5, 'product-phone-04.jpg', 1), (5, 'product-phone-01.jpg', 0), (5, 'product-phone-02.jpg', 0),
    (6, 'product-phone-05.jpg', 1), (6, 'product-phone-01.jpg', 0), (6, 'product-phone-02.jpg', 0),
    (7, 'product-phone-05.jpg', 1), (7, 'product-phone-01.jpg', 0), (7, 'product-phone-02.jpg', 0),
    (8, 'product-phone-03.jpg', 1), (8, 'product-phone-01.jpg', 0), (8, 'product-phone-02.jpg', 0),
    (9, 'product-accessory-01.jpg', 1), (9, 'product-accessory-02.jpg', 0), (9, 'product-accessory-03.jpg', 0),
    (10, 'product-accessory-02.jpg', 1), (10, 'product-accessory-01.jpg', 0), (10, 'product-accessory-03.jpg', 0),
    (11, 'product-accessory-03.jpg', 1), (11, 'product-accessory-01.jpg', 0), (11, 'product-accessory-02.jpg', 0),
    (12, 'product-camera-02.jpg', 1), (12, 'product-camera-01.jpg', 0), (12, 'product-camera-04.jpg', 0),
    (13, 'product-camera-05.png', 1), (13, 'product-camera-01.jpg', 0), (13, 'product-camera-02.jpg', 0),
    (14, 'product-camera-04.jpg', 1), (14, 'product-camera-01.jpg', 0), (14, 'product-camera-02.jpg', 0),
    (15, 'product-camera-01.jpg', 1), (15, 'product-camera-02.jpg', 0), (15, 'product-camera-04.jpg', 0),
    (16, 'product-pc-yasuo-01.jpg', 1), (16, 'product-pc-yasuo-02.jpg', 0), (16, 'product-pc-yasuo-03.jpg', 0),
    (17, 'product-pc-storm-01.png', 1), (17, 'product-pc-storm-02.jpg', 0), (17, 'product-pc-storm-03.jpg', 0),
    (18, 'product-pc-karmish-01.png', 1), (18, 'product-pc-karmish-02.jpg', 0), (18, 'product-pc-karmish-03.jpg', 0),
    (19, 'product-mouse-01.jpg', 1), (19, 'product-mouse-02.jpg', 0), (19, 'product-mouse-03.jpg', 0),
    (20, 'product-keyboard-02.jpg', 1), (20, 'product-keyboard-01.jpg', 0), (20, 'product-keyboard-03.jpg', 0),
    (21, 'product-controller-03.jpg', 1), (21, 'product-controller-01.jpg', 0), (21, 'product-controller-02.jpg', 0),
    (22, 'product-component-ssd-patriot-p300-512gb.jpg', 1),
    (23, 'product-component-ram-sstc-8gb-3200.png', 1),
    (24, 'product-component-ram-vsp-16gb-3200.jpg', 1);
