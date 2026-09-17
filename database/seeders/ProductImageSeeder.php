<?php

declare(strict_types=1);

final class ProductImageSeeder
{
    private const IMAGE_POOLS = [
        'laptop' => [
            'product-laptop-01.jpg',
            'product-laptop-02.jpg',
            'product-laptop-03.jpg',
            'product-laptop-04.jpg',
        ],
        'dien-thoai' => [
            'product-phone-01.jpg',
            'product-phone-02.jpg',
            'product-phone-03.jpg',
            'product-phone-04.jpg',
            'product-phone-05.jpg',
        ],
        'may-anh' => [
            'product-camera-01.jpg',
            'product-camera-02.jpg',
            'product-camera-04.jpg',
            'product-camera-05.png',
        ],
        'phu-kien' => [
            'product-accessory-01.jpg',
            'product-accessory-02.jpg',
            'product-accessory-03.jpg',
        ],
        'pc-yasuo' => [
            'product-pc-yasuo-01.jpg',
            'product-pc-yasuo-02.jpg',
            'product-pc-yasuo-03.jpg',
        ],
        'pc-storm' => [
            'product-pc-storm-01.png',
            'product-pc-storm-02.jpg',
            'product-pc-storm-03.jpg',
        ],
        'pc-karmish' => [
            'product-pc-karmish-01.png',
            'product-pc-karmish-02.jpg',
            'product-pc-karmish-03.jpg',
        ],
        'chuot-gaming' => [
            'product-mouse-01.jpg',
            'product-mouse-02.jpg',
            'product-mouse-03.jpg',
        ],
        'ban-phim' => [
            'product-keyboard-01.jpg',
            'product-keyboard-02.jpg',
            'product-keyboard-03.jpg',
        ],
        'tay-cam' => [
            'product-controller-01.jpg',
            'product-controller-02.jpg',
            'product-controller-03.jpg',
        ],
        'component-ssd-patriot-p300-512gb' => [
            'product-component-ssd-patriot-p300-512gb.jpg',
        ],
        'component-ram-sstc-8gb-3200' => [
            'product-component-ram-sstc-8gb-3200.png',
        ],
        'component-ram-vsp-16gb-3200' => [
            'product-component-ram-vsp-16gb-3200.jpg',
        ],
    ];

    /** Ảnh đại diện được chọn riêng để khớp sát nhất với tên từng sản phẩm. */
    private const PRIMARY_IMAGES = [
        'laptop-van-phong-mong-nhe' => 'product-laptop-01.jpg',
        'laptop-lap-trinh-hieu-nang-cao' => 'product-laptop-04.jpg',
        'laptop-hoc-tap-14-inch' => 'product-laptop-01.jpg',
        'laptop-man-hinh-lon-15-inch' => 'product-laptop-03.jpg',
        'dien-thoai-man-hinh-tran-vien' => 'product-phone-04.jpg',
        'dien-thoai-thong-minh-mau-den' => 'product-phone-05.jpg',
        'dien-thoai-thong-minh-camera-kep' => 'product-phone-05.jpg',
        'dien-thoai-thiet-ke-mong-nhe' => 'product-phone-03.jpg',
        'tai-nghe-chup-tai-khong-day' => 'product-accessory-01.jpg',
        'tai-nghe-chup-tai-chong-on' => 'product-accessory-02.jpg',
        'tai-nghe-chup-tai-studio' => 'product-accessory-03.jpg',
        'may-anh-ong-kinh-roi-mau-den' => 'product-camera-02.jpg',
        'may-anh-danh-cho-nguoi-moi' => 'product-camera-05.png',
        'may-anh-du-lich-nho-gon' => 'product-camera-04.jpg',
        'may-anh-chup-anh-chuyen-nghiep' => 'product-camera-01.jpg',
        'pc-gaming-yasuo' => 'product-pc-yasuo-01.jpg',
        'pc-gaming-storm-c' => 'product-pc-storm-01.png',
        'pc-gaming-karmish' => 'product-pc-karmish-01.png',
        'chuot-gaming-rgb-co-day' => 'product-mouse-01.jpg',
        'ban-phim-co-gaming-rgb' => 'product-keyboard-02.jpg',
        'tay-cam-choi-game-khong-day' => 'product-controller-03.jpg',
        'ssd-patriot-p300-512gb' => 'product-component-ssd-patriot-p300-512gb.jpg',
        'ram-sstc-8gb-ddr4-3200' => 'product-component-ram-sstc-8gb-3200.png',
        'ram-vsp-16gb-ddr4-3200' => 'product-component-ram-vsp-16gb-3200.jpg',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Gắn ảnh đúng nhóm sản phẩm. Ảnh người dùng tự tải lên luôn được giữ lại.
     */
    public function run(): void
    {
        $products = $this->pdo->query(
            'SELECT p.id, p.slug, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             ORDER BY p.id ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        $findImages = $this->pdo->prepare(
            'SELECT image_path, is_primary
             FROM product_images
             WHERE product_id = :product_id
             ORDER BY id ASC'
        );
        $insertImage = $this->pdo->prepare(
            'INSERT INTO product_images (product_id, image_path, is_primary)
             VALUES (:product_id, :image_path, :is_primary)'
        );
        $deleteSampleImages = $this->pdo->prepare(
            "DELETE FROM product_images
             WHERE product_id = :product_id AND image_path LIKE 'product-%'"
        );

        foreach ($products as $index => $product) {
            $productId = (int) $product['id'];
            $findImages->execute(['product_id' => $productId]);
            $existingImages = $findImages->fetchAll(PDO::FETCH_ASSOC);

            $sampleImageCount = count(array_filter(
                $existingImages,
                static fn (array $image): bool => str_starts_with((string) $image['image_path'], 'product-')
            ));
            if ($existingImages !== [] && $sampleImageCount === count($existingImages)) {
                $deleteSampleImages->execute(['product_id' => $productId]);
                $existingImages = [];
            }

            $categorySlug = (string) ($product['category_slug'] ?? '');
            $desiredImageCount = $categorySlug === 'linh-kien' ? 1 : 3;
            if (count($existingImages) >= $desiredImageCount) {
                continue;
            }

            $existingPaths = array_column($existingImages, 'image_path');
            $hasPrimary = array_sum(array_map(
                static fn (array $image): int => (int) $image['is_primary'],
                $existingImages
            )) > 0;
            $productSlug = (string) ($product['slug'] ?? '');
            $poolKey = match (true) {
                $productSlug === 'pc-gaming-yasuo' => 'pc-yasuo',
                $productSlug === 'pc-gaming-storm-c' => 'pc-storm',
                $productSlug === 'pc-gaming-karmish' => 'pc-karmish',
                str_starts_with($productSlug, 'chuot-gaming-') => 'chuot-gaming',
                str_starts_with($productSlug, 'ban-phim-') => 'ban-phim',
                str_starts_with($productSlug, 'tay-cam-') => 'tay-cam',
                $productSlug === 'ssd-patriot-p300-512gb' => 'component-ssd-patriot-p300-512gb',
                $productSlug === 'ram-sstc-8gb-ddr4-3200' => 'component-ram-sstc-8gb-3200',
                $productSlug === 'ram-vsp-16gb-ddr4-3200' => 'component-ram-vsp-16gb-3200',
                default => $categorySlug,
            };
            $pool = self::IMAGE_POOLS[$poolKey] ?? self::IMAGE_POOLS['phu-kien'];
            $primaryImage = self::PRIMARY_IMAGES[$productSlug] ?? null;
            if ($primaryImage !== null && in_array($primaryImage, $pool, true)) {
                $pool = array_values(array_unique([$primaryImage, ...$pool]));
                $startIndex = 0;
            } else {
                $startIndex = ($index * 2) % count($pool);
            }

            for ($offset = 0; count($existingPaths) < $desiredImageCount && $offset < count($pool); $offset++) {
                $imagePath = $pool[($startIndex + $offset) % count($pool)];
                if (in_array($imagePath, $existingPaths, true)) {
                    continue;
                }

                $insertImage->execute([
                    'product_id' => $productId,
                    'image_path' => $imagePath,
                    'is_primary' => $hasPrimary ? 0 : 1,
                ]);
                $existingPaths[] = $imagePath;
                $hasPrimary = true;
            }
        }
    }
}
