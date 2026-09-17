<?php
class ProductModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy tất cả sản phẩm kèm tên danh mục
    public function getAllProducts() {
        $sql = "SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách danh mục để hiển thị ở select box
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin 1 danh mục theo ID (Dùng để kiểm tra danh mục có tồn tại hay không)
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy danh mục theo slug để các liên kết như ?category=phu-kien hoạt động ổn định.
    public function getCategoryBySlug(string $slug) {
        $sql = "SELECT * FROM categories WHERE slug = ? AND status = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy 1 sản phẩm theo ID
    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kiểm tra trùng Slug
    public function isSlugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) FROM products WHERE slug = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() > 0;
    }

    // Thêm sản phẩm mới (Đã có description)
    public function insertProduct($data, array $imagePaths = []): int {
        $this->pdo->beginTransaction();

        try {
            $sql = "INSERT INTO products (category_id, name, slug, price, stock, description, status)
                    VALUES (:category_id, :name, :slug, :price, :stock, :description, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':category_id' => $data['category_id'],
                ':name'        => $data['name'],
                ':slug'        => $data['slug'],
                ':price'       => $data['price'],
                ':stock'       => $data['stock'],
                ':description' => $data['description'],
                ':status'      => $data['status']
            ]);

            $productId = (int) $this->pdo->lastInsertId();
            $this->insertProductImages($productId, $imagePaths);
            $this->pdo->commit();

            return $productId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // Cập nhật sản phẩm (Đã có description)
    public function updateProduct($id, $data, array $newImagePaths = [], array $deleteImageIds = []): array {
        $this->pdo->beginTransaction();

        try {
            $sql = "UPDATE products
                    SET category_id = :category_id,
                        name = :name,
                        slug = :slug,
                        price = :price,
                        stock = :stock,
                        description = :description,
                        status = :status
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'          => $id,
                ':category_id' => $data['category_id'],
                ':name'        => $data['name'],
                ':slug'        => $data['slug'],
                ':price'       => $data['price'],
                ':stock'       => $data['stock'],
                ':description' => $data['description'],
                ':status'      => $data['status']
            ]);

            $deletedPaths = $this->deleteProductImages((int) $id, $deleteImageIds);
            $this->insertProductImages((int) $id, $newImagePaths);
            $this->ensurePrimaryImage((int) $id);
            $this->pdo->commit();

            return $deletedPaths;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getProductImages(int $productId): array {
        $stmt = $this->pdo->prepare(
            'SELECT id, product_id, image_path, is_primary
             FROM product_images
             WHERE product_id = ?
             ORDER BY is_primary DESC, id ASC'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function insertProductImages(int $productId, array $imagePaths): void {
        if ($imagePaths === []) {
            return;
        }

        $hasPrimaryStmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1'
        );
        $hasPrimaryStmt->execute([$productId]);
        $hasPrimary = (int) $hasPrimaryStmt->fetchColumn() > 0;
        $insert = $this->pdo->prepare(
            'INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)'
        );

        foreach ($imagePaths as $imagePath) {
            $insert->execute([$productId, $imagePath, $hasPrimary ? 0 : 1]);
            $hasPrimary = true;
        }
    }

    private function deleteProductImages(int $productId, array $imageIds): array {
        $imageIds = array_values(array_unique(array_filter(array_map('intval', $imageIds), static fn (int $id): bool => $id > 0)));
        if ($imageIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $params = array_merge([$productId], $imageIds);
        $select = $this->pdo->prepare(
            "SELECT image_path FROM product_images WHERE product_id = ? AND id IN ($placeholders)"
        );
        $select->execute($params);
        $paths = $select->fetchAll(PDO::FETCH_COLUMN);

        $delete = $this->pdo->prepare(
            "DELETE FROM product_images WHERE product_id = ? AND id IN ($placeholders)"
        );
        $delete->execute($params);

        return array_map('strval', $paths);
    }

    private function ensurePrimaryImage(int $productId): void {
        $primary = $this->pdo->prepare(
            'SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1'
        );
        $primary->execute([$productId]);
        if ((int) $primary->fetchColumn() > 0) {
            return;
        }

        $first = $this->pdo->prepare(
            'SELECT id FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1'
        );
        $first->execute([$productId]);
        $imageId = (int) $first->fetchColumn();
        if ($imageId > 0) {
            $setPrimary = $this->pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ?');
            $setPrimary->execute([$imageId]);
        }
    }

    // Xóa sản phẩm
    public function deleteProduct($id) {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ===== Thêm cho SHOP-22 (tìm kiếm) và SHOP-23 (lọc + sắp xếp) =====
    // Dùng cho trang danh sách sản phẩm phía khách hàng (views/products.php).
    // Không đụng tới các hàm phía trên (đang phục vụ trang quản trị).

    /**
     * Tìm + lọc + sắp xếp + phân trang sản phẩm đang bán (status = 1),
     * kèm ảnh đại diện (is_primary = 1) nếu có.
     */
    public function searchProducts(array $filters): array
    {
        $where = ['p.status = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE :keyword_name OR p.description LIKE :keyword_description OR c.name LIKE :keyword_category)';
            $keyword = '%' . $filters['q'] . '%';
            $params[':keyword_name'] = $keyword;
            $params[':keyword_description'] = $keyword;
            $params[':keyword_category'] = $keyword;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        $orderBy = 'p.created_at DESC';
        if (($filters['sort'] ?? '') === 'price_asc') {
            $orderBy = 'p.price ASC';
        } elseif (($filters['sort'] ?? '') === 'price_desc') {
            $orderBy = 'p.price DESC';
        }

        $limit = (int) ($filters['limit'] ?? 12);
        $offset = (int) ($filters['offset'] ?? 0);

        $sql = "SELECT p.*, c.name AS category_name, pi.image_path
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số sản phẩm khớp bộ lọc (dùng để tính số trang phân trang).
     */
    public function countSearchProducts(array $filters): int
    {
        $where = ['p.status = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE :keyword_name OR p.description LIKE :keyword_description OR c.name LIKE :keyword_category)';
            $keyword = '%' . $filters['q'] . '%';
            $params[':keyword_name'] = $keyword;
            $params[':keyword_description'] = $keyword;
            $params[':keyword_category'] = $keyword;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        $sql = 'SELECT COUNT(*)
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE ' . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
?>
