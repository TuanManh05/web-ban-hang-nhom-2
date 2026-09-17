<?php

declare(strict_types=1);

require_once __DIR__ . '/../factories/ProductFactory.php';

final class ProductCatalogSeeder
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Tạo mới hoặc đồng bộ danh mục sản phẩm mẫu mà không làm đổi ID sản phẩm cũ.
     */
    public function run(): void
    {
        $categoryRows = $this->pdo
            ->query('SELECT id, slug FROM categories WHERE status = 1 ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);
        $categoryIdsBySlug = [];
        foreach ($categoryRows as $category) {
            $categoryIdsBySlug[(string) $category['slug']] = (int) $category['id'];
        }

        $catalog = ProductFactory::generate($categoryIdsBySlug);
        $sampleIds = $this->pdo->query(
            "SELECT id FROM products
             WHERE description LIKE 'Sản phẩm mẫu phục vụ phát triển:%'
             ORDER BY id ASC"
        )->fetchAll(PDO::FETCH_COLUMN);

        $update = $this->pdo->prepare(
            'UPDATE products SET
                category_id = :category_id, name = :name, slug = :slug,
                description = :description, price = :price, stock = :stock, status = :status
             WHERE id = :id'
        );
        $insert = $this->pdo->prepare(
            'INSERT INTO products
                (category_id, name, slug, description, price, stock, status)
             VALUES
                (:category_id, :name, :slug, :description, :price, :stock, :status)'
        );

        foreach ($catalog as $index => $product) {
            if (isset($sampleIds[$index])) {
                $update->execute(['id' => (int) $sampleIds[$index]] + $product);
                continue;
            }

            $insert->execute($product);
        }
    }
}
