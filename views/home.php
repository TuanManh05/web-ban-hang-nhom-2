<?php require __DIR__ . '/partials/header.php'; ?>

<section class="home-shell">
    <div class="container py-3 py-lg-4">
        <div class="hero-layout">
            <aside class="home-categories">
                <h2><span>☰</span> Danh mục nổi bật</h2>
                <a href="<?= $basePath ?>/views/products.php?category=pc-gaming"><span>🖥</span> PC Gaming <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?category=laptop"><span>▰</span> Laptop <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?q=CPU"><span>▣</span> CPU - Bộ vi xử lý <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?q=VGA"><span>▤</span> VGA - Card màn hình <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?q=RAM"><span>▥</span> RAM - Bộ nhớ <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?q=SSD"><span>▱</span> SSD - Ổ cứng <b>›</b></a>
                <a href="<?= $basePath ?>/views/products.php?category=phu-kien"><span>⌨</span> Gaming Gear <b>›</b></a>
            </aside>

            <div class="main-promo">
                <div class="promo-copy">
                    <span class="promo-label">ĐẠI TIỆC CÔNG NGHỆ</span>
                    <h1>NÂNG CẤP<br><em>HIỆU NĂNG</em></h1>
                    <p>Giá tốt bất ngờ · Sản phẩm chính hãng<br>Ưu đãi dành riêng cho khách hàng Nhóm 2</p>
                    <a href="<?= $basePath ?>/views/products.php" class="promo-button">MUA NGAY <span>→</span></a>
                </div>
                <div class="promo-visual" aria-hidden="true">
                    <div class="monitor"><div class="monitor-screen"><span>N2</span></div><i></i></div>
                    <div class="keyboard"></div>
                    <div class="glow-orb orb-one"></div><div class="glow-orb orb-two"></div>
                </div>
            </div>

            <div class="side-promos">
                <a href="<?= $basePath ?>/views/products.php?sort=price_asc" class="side-promo red-promo">
                    <small>DEAL GIÁ SỐC</small><strong>GIẢM ĐẾN<br>30%</strong><span>Săn ngay hôm nay →</span>
                </a>
                <a href="<?= $basePath ?>/views/products.php?category=laptop" class="side-promo dark-promo">
                    <small>LAPTOP HỌC TẬP</small><strong>NHẸ NHÀNG<br>MẠNH MẼ</strong><span>Khám phá ngay →</span>
                </a>
            </div>
        </div>

        <div class="benefit-row">
            <div><span>⚡</span><p><strong>Giá tốt mỗi ngày</strong><small>Nhiều lựa chọn phù hợp</small></p></div>
            <div><span>🛡</span><p><strong>Bảo hành rõ ràng</strong><small>An tâm khi mua sắm</small></p></div>
            <div><span>💳</span><p><strong>Thanh toán tiện lợi</strong><small>Quy trình nhanh chóng</small></p></div>
            <div><span>🎧</span><p><strong>Tư vấn nhiệt tình</strong><small>Luôn sẵn sàng hỗ trợ</small></p></div>
        </div>
    </div>
</section>

<section class="container pb-5">
    <div class="deal-heading">
        <div><span class="deal-icon">⚡</span><h2>SẢN PHẨM NỔI BẬT</h2><small>Giá tốt – chốt đơn ngay</small></div>
        <a href="<?= $basePath ?>/views/products.php">Xem tất cả <span>→</span></a>
    </div>

    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $index => $product): ?>
                <?php $stock = (int) ($product['stock'] ?? 0); ?>
                <article class="product-card">
                    <div class="product-badges"><span><?= $index < 3 ? 'HOT' : 'MỚI' ?></span><?php if ($stock > 0): ?><small>Còn hàng</small><?php endif; ?></div>
                    <a href="<?= $basePath ?>/views/product-detail.php?id=<?= urlencode((string) $product['id']) ?>" class="product-image">
                        <img src="<?= !empty($product['image_path'])
                            ? htmlspecialchars($basePath . '/uploads/' . $product['image_path'], ENT_QUOTES, 'UTF-8')
                            : $basePath . '/assets/img/tech-placeholder.svg' ?>"
                             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                    <div class="product-info">
                        <small class="product-category"><?= htmlspecialchars((string) ($product['category_name'] ?? 'Sản phẩm công nghệ'), ENT_QUOTES, 'UTF-8') ?></small>
                        <h3><a href="<?= $basePath ?>/views/product-detail.php?id=<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                        <div class="rating"><span>★★★★★</span><small>(<?= 8 + $index * 3 ?> đánh giá)</small></div>
                        <p class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?>₫</p>
                        <p class="old-price"><?= number_format((float) $product['price'] * 1.12, 0, ',', '.') ?>₫</p>
                        <?php if ($stock > 0): ?>
                            <form method="post" action="<?= $basePath ?>/views/cart.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1"><input type="hidden" name="redirect" value="<?= $basePath ?>/index.php">
                                <button type="submit" class="add-cart-button"><span>🛒</span> THÊM VÀO GIỎ</button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="add-cart-button disabled" disabled>HẾT HÀNG</button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-products">Chưa có sản phẩm nào để hiển thị.</div>
        <?php endif; ?>
    </div>
</section>

<section class="container pb-5">
    <div class="category-showcase">
        <div><span>🖥</span><strong>PC Gaming</strong><a href="<?= $basePath ?>/views/products.php?category=pc-gaming">Xem sản phẩm</a></div>
        <div><span>▰</span><strong>Laptop</strong><a href="<?= $basePath ?>/views/products.php?category=laptop">Xem sản phẩm</a></div>
        <div><span>▣</span><strong>Linh kiện</strong><a href="<?= $basePath ?>/views/products.php?q=CPU">Xem sản phẩm</a></div>
        <div><span>⌨</span><strong>Phụ kiện</strong><a href="<?= $basePath ?>/views/products.php?category=phu-kien">Xem sản phẩm</a></div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
