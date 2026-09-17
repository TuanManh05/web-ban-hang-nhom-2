<?php

declare(strict_types=1);

final class ProductFactory
{
    /**
     * Danh mục cố định để tên, loại sản phẩm và hình ảnh luôn khớp nhau.
     */
    private const CATALOG = [
        ['laptop', 'Laptop Mỏng Nhẹ', 'laptop-van-phong-mong-nhe', 15990000, 18, 'Laptop thiết kế gọn nhẹ, phù hợp học tập và công việc văn phòng hằng ngày.'],
        ['laptop', 'Laptop Lập Trình', 'laptop-lap-trinh-hieu-nang-cao', 23990000, 12, 'Laptop hiệu năng cao dành cho lập trình, học tập và xử lý nhiều tác vụ.'],
        ['laptop', 'Laptop Học Tập 14"', 'laptop-hoc-tap-14-inch', 12990000, 25, 'Laptop 14 inch nhỏ gọn, đáp ứng nhu cầu học trực tuyến và làm bài tập.'],
        ['laptop', 'Laptop 15"', 'laptop-man-hinh-lon-15-inch', 18990000, 15, 'Laptop màn hình lớn, không gian hiển thị rộng và bàn phím thuận tiện khi làm việc.'],
        ['dien-thoai', 'Điện Thoại Tràn Viền', 'dien-thoai-man-hinh-tran-vien', 13990000, 20, 'Điện thoại thông minh với màn hình lớn, hiển thị rõ và thao tác cảm ứng thuận tiện.'],
        ['dien-thoai', 'Điện Thoại Đen', 'dien-thoai-thong-minh-mau-den', 11990000, 22, 'Điện thoại màu đen với thiết kế hiện đại, phù hợp nhu cầu liên lạc và giải trí.'],
        ['dien-thoai', 'Điện Thoại Camera Kép', 'dien-thoai-thong-minh-camera-kep', 16990000, 14, 'Điện thoại thông minh trang bị cụm camera kép và màn hình sắc nét.'],
        ['dien-thoai', 'Điện Thoại Slim', 'dien-thoai-thiet-ke-mong-nhe', 9990000, 28, 'Điện thoại có thiết kế mỏng nhẹ, dễ cầm nắm và mang theo hằng ngày.'],
        ['phu-kien', 'Tai Nghe Wireless', 'tai-nghe-chup-tai-khong-day', 1490000, 30, 'Tai nghe chụp tai không dây, đệm tai êm và phù hợp nghe nhạc hằng ngày.'],
        ['phu-kien', 'Tai Nghe Chống Ồn', 'tai-nghe-chup-tai-chong-on', 2290000, 16, 'Tai nghe chụp tai hỗ trợ giảm tiếng ồn, thích hợp làm việc và giải trí.'],
        ['phu-kien', 'Tai Nghe Studio', 'tai-nghe-chup-tai-studio', 1890000, 19, 'Tai nghe chụp tai kiểu Studio với âm thanh rõ và thiết kế chắc chắn.'],
        ['may-anh', 'Máy Ảnh Lens Rời', 'may-anh-ong-kinh-roi-mau-den', 18490000, 10, 'Máy ảnh ống kính rời màu đen, phù hợp chụp ảnh chân dung và phong cảnh.'],
        ['may-anh', 'Máy Ảnh Cơ Bản', 'may-anh-danh-cho-nguoi-moi', 12990000, 13, 'Máy ảnh dễ sử dụng cho người mới bắt đầu học chụp ảnh.'],
        ['may-anh', 'Máy Ảnh Du Lịch', 'may-anh-du-lich-nho-gon', 15490000, 11, 'Máy ảnh nhỏ gọn, thuận tiện mang theo trong các chuyến đi.'],
        ['may-anh', 'Máy Ảnh Pro', 'may-anh-chup-anh-chuyen-nghiep', 27990000, 8, 'Máy ảnh hiệu năng cao dành cho nhu cầu chụp ảnh chuyên nghiệp.'],
        ['pc-gaming', 'PC Gaming Yasuo', 'pc-gaming-yasuo', 17247000, 9, 'Intel Core i5-12400F, RTX 3050 6GB, RAM 16GB và SSD 500GB; phù hợp chơi game Full HD.'],
        ['pc-gaming', 'PC Gaming Storm C', 'pc-gaming-storm-c', 25737000, 7, 'Intel Core i5-12400F, RTX 5060, RAM 16GB và SSD 512GB; đáp ứng tốt eSports và game AAA.'],
        ['pc-gaming', 'PC Gaming Karmish', 'pc-gaming-karmish', 30451000, 5, 'Intel Core i5-14400F, RTX 5060, RAM DDR5 16GB, SSD 512GB và Wi-Fi.'],
        ['phu-kien', 'Chuột Gaming RGB', 'chuot-gaming-rgb-co-day', 690000, 32, 'Chuột gaming có dây với đèn RGB, cảm biến chính xác và thiết kế thuận tay cho các phiên chơi dài.'],
        ['phu-kien', 'Bàn Phím Cơ RGB', 'ban-phim-co-gaming-rgb', 1490000, 24, 'Bàn phím cơ gaming có đèn RGB, phản hồi phím rõ và bố cục thuận tiện cho chơi game lẫn làm việc.'],
        ['phu-kien', 'Tay Cầm Không Dây', 'tay-cam-choi-game-khong-day', 1290000, 20, 'Tay cầm chơi game không dây với hai cần analog, bố cục nút quen thuộc và kết nối ổn định.'],
        ['linh-kien', 'SSD Patriot P300 512GB', 'ssd-patriot-p300-512gb', 760000, 18, 'SSD M.2 2280 NVMe PCIe Gen3 x4, dung lượng 512GB, tốc độ đọc/ghi tối đa khoảng 1700/1200MB/s.'],
        ['linh-kien', 'RAM SSTC 8GB DDR4 3200', 'ram-sstc-8gb-ddr4-3200', 1350000, 24, 'RAM PC 8GB (1x8GB), chuẩn DDR4, bus 3200MHz, tương thích nền tảng Intel và AMD.'],
        ['linh-kien', 'RAM VSP 16GB DDR4 3200', 'ram-vsp-16gb-ddr4-3200', 2490000, 16, 'RAM PC 16GB (1x16GB), chuẩn DDR4, bus 3200MHz, hỗ trợ Intel và AMD.'],
    ];

    public static function generate(array $categoryIdsBySlug): array
    {
        $products = [];

        foreach (self::CATALOG as [$categorySlug, $name, $slug, $price, $stock, $description]) {
            if (!isset($categoryIdsBySlug[$categorySlug])) {
                throw new RuntimeException('Thiếu danh mục cho sản phẩm mẫu: ' . $categorySlug);
            }

            $products[] = [
                'category_id' => (int) $categoryIdsBySlug[$categorySlug],
                'name' => $name,
                'slug' => $slug,
                'price' => $price,
                'stock' => $stock,
                'status' => 1,
                'description' => 'Sản phẩm mẫu phục vụ phát triển: ' . $description,
            ];
        }

        return $products;
    }
}
