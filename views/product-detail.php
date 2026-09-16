<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $id > 0 ? Product::findById($id) : null;
$pageTitle = $product ? $product['name'] : 'Sản phẩm không tồn tại';
require __DIR__ . '/partials/header.php';
?>
<section class="container product-detail-shell">
    <p class="small text-secondary mb-3"><a class="text-decoration-none" href="<?= $basePath ?>/index.php">Trang chủ</a> / <a class="text-decoration-none" href="<?= $basePath ?>/views/products.php">Sản phẩm</a><?= $product ? ' / ' . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') : '' ?></p>
    <?php if ($product): ?>
        <div class="product-detail-card row g-4 g-lg-5">
            <?php
            $galleryImages = $product['images'] ?? [];
            if ($galleryImages === []) {
                $galleryImages = [['image_path' => null, 'is_primary' => 1]];
            }
            $mainImagePath = !empty($galleryImages[0]['image_path'])
                ? $basePath . '/uploads/' . $galleryImages[0]['image_path']
                : $basePath . '/assets/img/tech-placeholder.svg';
            ?>
            <div class="col-md-5">
                <div class="product-gallery">
                    <div class="detail-image-frame">
                        <img id="productMainImage"
                             src="<?= htmlspecialchars($mainImagePath, ENT_QUOTES, 'UTF-8') ?>"
                             class="detail-image"
                             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <?php if (count($galleryImages) > 1): ?>
                        <div class="detail-thumbnails" aria-label="Ảnh sản phẩm">
                            <?php foreach ($galleryImages as $index => $image): ?>
                                <?php $imagePath = $basePath . '/uploads/' . $image['image_path']; ?>
                                <button type="button"
                                        class="detail-thumbnail<?= $index === 0 ? ' active' : '' ?>"
                                        data-gallery-image="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-label="Xem ảnh <?= $index + 1 ?> của <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                                    <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Ảnh <?= $index + 1 ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <span class="detail-eyebrow"><?= htmlspecialchars((string) ($product['category_name'] ?? 'Sản phẩm công nghệ'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="detail-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="rating mb-2"><span>★★★★★</span><small>4.9 · Sản phẩm chính hãng</small></div>
                <div class="detail-price"><?= number_format((float) $product['price'], 0, ',', '.') ?>₫</div>
                <p class="text-secondary lh-lg"><?= nl2br(htmlspecialchars((string) ($product['description'] ?? 'Sản phẩm công nghệ chất lượng, phù hợp cho nhu cầu học tập, làm việc và giải trí.'), ENT_QUOTES, 'UTF-8')) ?></p>
                <span class="detail-stock">✓ Còn <?= (int) $product['stock'] ?> sản phẩm</span>
                <div class="border-top mt-4 pt-4">
                    <?php if ((int) $product['status'] === 1 && (int) $product['stock'] > 0): ?>
                        <form method="post" action="<?= $basePath ?>/views/cart.php" class="d-flex flex-wrap gap-2 mb-3">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="redirect" value="product-detail.php?id=<?= (int) $product['id'] ?>">
                            <input class="form-control" style="max-width:100px" type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" aria-label="Số lượng">
                            <button class="btn btn-primary px-4 fw-bold" type="submit">🛒 Thêm vào giỏ hàng</button>
                        </form>
                    <?php else: ?><p class="text-danger fw-semibold">Sản phẩm hiện đã hết hàng.</p><?php endif; ?>
                    <div class="row g-2 mt-2 small"><div class="col-sm-6"><div class="border rounded p-3">🚚 Giao hàng nhanh toàn quốc</div></div><div class="col-sm-6"><div class="border rounded p-3">🛡 Bảo hành rõ ràng</div></div></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="product-detail-card text-center py-5"><h1 class="h4 fw-bold mb-3">Sản phẩm không tồn tại</h1><p class="text-secondary">Sản phẩm bạn tìm có thể đã bị xoá hoặc đường dẫn không đúng.</p><a href="<?= $basePath ?>/index.php" class="btn btn-primary">Về trang chủ</a></div>
    <?php endif; ?>
</section>
<script src="<?= $basePath ?>/assets/js/product-gallery.js"></script>
<?php require __DIR__ . '/partials/footer.php'; ?>
