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
            'product-laptop-05.jpg',
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
            'product-camera-03.jpg',
            'product-camera-04.jpg',
            'product-camera-05.png',
        ],
        'phu-kien' => [
            'product-accessory-01.jpg',
            'product-accessory-02.jpg',
            'product-accessory-03.jpg',
            'product-accessory-04.jpg',
            'product-accessory-05.jpg',
        ],
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Bổ sung tối đa ba ảnh mẫu cho mỗi sản phẩm mà không xóa ảnh đã có.
     */
    public function run(): void
    {
        $products = $this->pdo->query(
            'SELECT p.id, c.slug AS category_slug
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

        foreach ($products as $index => $product) {
            $productId = (int) $product['id'];
            $findImages->execute(['product_id' => $productId]);
            $existingImages = $findImages->fetchAll(PDO::FETCH_ASSOC);

            if (count($existingImages) >= 3) {
                continue;
            }

            $existingPaths = array_column($existingImages, 'image_path');
            $hasPrimary = array_sum(array_map(
                static fn (array $image): int => (int) $image['is_primary'],
                $existingImages
            )) > 0;
            $categorySlug = (string) ($product['category_slug'] ?? '');
            $pool = self::IMAGE_POOLS[$categorySlug] ?? self::IMAGE_POOLS['phu-kien'];
            $startIndex = ($index * 2) % count($pool);

            for ($offset = 0; count($existingPaths) < 3 && $offset < count($pool); $offset++) {
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
