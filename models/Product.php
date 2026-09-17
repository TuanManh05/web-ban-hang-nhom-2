<?php

declare(strict_types=1);

final class Product
{
    /**
     * Lấy các danh mục đang hiển thị và thực sự có sản phẩm để menu trang chủ
     * không dẫn tới những bộ lọc rỗng hoặc danh mục đã bị tắt.
     */
    public static function featuredCategories(): array
    {
        $pdo = database();

        $sql = "SELECT c.name, c.slug, COUNT(p.id) AS product_count
                FROM categories c
                INNER JOIN products p
                    ON p.category_id = c.id AND p.status = 1
                WHERE c.status = 1
                GROUP BY c.id, c.name, c.slug
                HAVING COUNT(p.id) > 0
                ORDER BY FIELD(c.slug, 'pc-gaming', 'laptop', 'linh-kien', 'phu-kien', 'dien-thoai', 'may-anh'),
                         c.name ASC";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm còn bán, kèm ảnh đại diện (is_primary = 1) nếu có.
     */
    public static function featured(int $limit = 8): array
    {
        $pdo = database();

        $sql = 'SELECT p.id, p.name, p.slug, p.price, p.stock, p.status,
                       c.name AS category_name, pi.image_path
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN product_images pi
                    ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.status = 1
                ORDER BY p.created_at DESC
                LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = database();

        $stmt = $pdo->prepare(
            'SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.status = 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return null;
        }

        $imageStatement = $pdo->prepare(
            'SELECT image_path, is_primary
             FROM product_images
             WHERE product_id = :product_id
             ORDER BY is_primary DESC, id ASC'
        );
        $imageStatement->execute(['product_id' => $id]);
        $product['images'] = $imageStatement->fetchAll(PDO::FETCH_ASSOC);
        $product['image_path'] = $product['images'][0]['image_path'] ?? null;

        return $product;
    }
}
