<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ProductModel.php';

$pdo = database();
$productModel = new ProductModel($pdo);
$keyword = trim((string) ($_GET['q'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$sort = (string) ($_GET['sort'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$categories = $productModel->getAllCategories();
$selectedCategoryName = '';

if ($categoryId <= 0 && $categorySlug !== '') {
    $category = $productModel->getCategoryBySlug($categorySlug);
    if ($category !== false) {
        $categoryId = (int) $category['id'];
        $selectedCategoryName = (string) $category['name'];
    }
}

if ($categoryId > 0 && $selectedCategoryName === '') {
    foreach ($categories as $category) {
        if ((int) $category['id'] === $categoryId) {
            $selectedCategoryName = (string) $category['name'];
            break;
        }
    }
}

$filters = ['q' => $keyword, 'category_id' => $categoryId ?: null, 'sort' => $sort, 'limit' => $perPage, 'offset' => ($page - 1) * $perPage];
$products = $productModel->searchProducts($filters);
$totalProducts = $productModel->countSearchProducts($filters);
$totalPages = (int) ceil($totalProducts / $perPage);
$pageTitle = 'Sản phẩm';
require __DIR__ . '/partials/header.php';
?>
<section class="container catalog-page">
    <div class="catalog-title">
        <div><p>Trang chủ / Sản phẩm</p><h1><?php if ($keyword !== ''): ?>Kết quả cho “<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>”<?php elseif ($selectedCategoryName !== ''): ?><?= htmlspecialchars($selectedCategoryName, ENT_QUOTES, 'UTF-8') ?><?php else: ?>TẤT CẢ SẢN PHẨM<?php endif; ?></h1></div>
        <p><?= $totalProducts ?> sản phẩm</p>
    </div>

    <form method="get" class="filter-panel row g-2" id="catalogFilters" autocomplete="off"
          data-category-id="<?= $categoryId > 0 ? $categoryId : '' ?>"
          data-sort="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
        <div class="col-12 col-md-5"><input type="search" name="q" class="form-control" placeholder="Tìm theo tên, mô tả hoặc danh mục..." value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-6 col-md-3"><select name="category_id" class="form-select"><option value="">Tất cả danh mục</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="col-6 col-md-3"><select name="sort" class="form-select"><option value="" <?= $sort === '' ? 'selected' : '' ?>>Mới nhất</option><option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option><option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option></select></div>
        <div class="col-12 col-md-1 d-grid"><button type="submit" class="btn btn-primary">Lọc</button></div>
    </form>

    <div class="product-grid catalog-grid">
        <?php if ($products !== []): foreach ($products as $index => $product): ?>
            <article class="product-card">
                <div class="product-badges"><span><?= $index < 3 ? 'HOT' : 'MỚI' ?></span><?php if ((int) $product['stock'] > 0): ?><small>Còn hàng</small><?php endif; ?></div>
                <a class="product-image" href="<?= $basePath ?>/views/product-detail.php?id=<?= (int) $product['id'] ?>"><img src="<?= !empty($product['image_path']) ? htmlspecialchars($basePath . '/uploads/' . $product['image_path'], ENT_QUOTES, 'UTF-8') : $basePath . '/assets/img/tech-placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"></a>
                <div class="product-info">
                    <small class="product-category"><?= htmlspecialchars((string) ($product['category_name'] ?? 'Công nghệ'), ENT_QUOTES, 'UTF-8') ?></small>
                    <h3><a href="<?= $basePath ?>/views/product-detail.php?id=<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                    <div class="rating"><span>★★★★★</span><small>(<?= 5 + $index * 2 ?> đánh giá)</small></div>
                    <p class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?>₫</p>
                    <p class="old-price"><?= number_format((float) $product['price'] * 1.12, 0, ',', '.') ?>₫</p>
                    <?php if ((int) $product['stock'] > 0): ?>
                        <form method="post" action="<?= $basePath ?>/views/cart.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="quantity" value="1"><input type="hidden" name="redirect" value="<?= $basePath ?>/views/products.php"><button class="add-cart-button" type="submit"><span>🛒</span> THÊM VÀO GIỎ</button></form>
                    <?php else: ?><button class="add-cart-button disabled" type="button" disabled>HẾT HÀNG</button><?php endif; ?>
                </div>
            </article>
        <?php endforeach; else: ?><div class="empty-products">Không tìm thấy sản phẩm phù hợp<?= $keyword !== '' ? ' với từ khoá “' . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . '”' : '' ?>.</div><?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?><nav class="mt-4"><ul class="pagination justify-content-center flex-wrap"><?php for ($i = 1; $i <= $totalPages; $i++): $query = http_build_query(['q' => $keyword, 'category_id' => $categoryId ?: '', 'sort' => $sort, 'page' => $i]); ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</section>
<script src="<?= $basePath ?>/assets/js/catalog-filters.js?v=2"></script>
<?php require __DIR__ . '/partials/footer.php'; ?>
