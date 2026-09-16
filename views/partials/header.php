<?php declare(strict_types=1);

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__ . '/../..');
$basePath = '';
if ($documentRoot && $projectRoot && str_starts_with($projectRoot, $documentRoot)) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

require_once __DIR__ . '/../../models/Cart.php';
$cartCount = Cart::getTotalQuantity();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
$isHome = str_ends_with($currentPath, '/index.php') && (($_GET['action'] ?? 'home') === 'home');
$isProducts = str_ends_with($currentPath, '/products.php');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e11d2e">
    <title><?= htmlspecialchars($pageTitle ?? 'Nhóm 2', ENT_QUOTES, 'UTF-8') ?> | Nhóm 2 Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $basePath ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
<header class="store-header">
    <div class="utility-bar">
        <div class="container d-flex justify-content-between align-items-center gap-3">
            <p class="mb-0 d-none d-md-block">Công nghệ chính hãng · Giá tốt mỗi ngày</p>
            <div class="utility-links ms-auto">
                <a href="#store-services">Khuyến mãi</a>
                <a href="#store-services">Chính sách</a>
                <span>Hotline: <strong>1900 6868</strong></span>
            </div>
        </div>
    </div>

    <div class="main-header">
        <div class="container header-grid">
            <a class="store-logo" href="<?= $basePath ?>/index.php" aria-label="Nhóm 2 Shop - Trang chủ">
                <span class="logo-mark">N2</span>
                <span><strong>NHÓM 2</strong><small>TECH STORE</small></span>
            </a>

            <form class="header-search" method="get" action="<?= $basePath ?>/views/products.php" role="search"
                  data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>"
                  data-suggest-url="<?= $basePath ?>/views/api/products-search.php" autocomplete="off">
                <input type="search" name="q" id="headerSearchInput" placeholder="Bạn cần tìm sản phẩm gì?"
                       aria-label="Tìm kiếm sản phẩm" aria-autocomplete="list" aria-expanded="false"
                       aria-controls="headerSearchSuggest" role="combobox"
                       value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" aria-label="Tìm kiếm"><span aria-hidden="true">⌕</span><span class="d-none d-sm-inline">Tìm kiếm</span></button>
                <div id="headerSearchSuggest" class="search-suggest-panel" role="listbox" hidden></div>
            </form>

            <div class="header-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="account-menu" tabindex="0">
                        <span class="action-icon" aria-hidden="true">♙</span>
                        <span class="action-copy"><small>Xin chào</small><strong><?= htmlspecialchars((string) ($_SESSION['user']['name'] ?? 'Tài khoản'), ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <div class="account-dropdown">
                            <a href="<?= $basePath ?>/index.php?action=profile">Tài khoản</a>
                            <a href="<?= $basePath ?>/index.php?action=orders">Đơn hàng của tôi</a>
                            <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                                <a href="<?= $basePath ?>/index.php?action=admin">Trang quản trị</a>
                            <?php endif; ?>
                            <form method="post" action="<?= $basePath ?>/index.php?action=logout">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="header-action" href="<?= $basePath ?>/index.php?action=login">
                        <span class="action-icon" aria-hidden="true">♙</span>
                        <span class="action-copy"><small>Đăng nhập</small><strong>Tài khoản</strong></span>
                    </a>
                <?php endif; ?>
                <a class="header-action cart-action" href="<?= $basePath ?>/views/cart.php">
                    <span class="action-icon" aria-hidden="true">🛒</span>
                    <span class="action-copy"><small>Giỏ hàng</small><strong><?= $cartCount ?> sản phẩm</strong></span>
                    <span class="cart-count"><?= $cartCount ?></span>
                </a>
            </div>
        </div>
    </div>

    <nav class="category-nav" aria-label="Điều hướng chính">
        <div class="container category-nav-inner">
            <a class="category-trigger" href="<?= $basePath ?>/views/products.php"><span>☰</span> DANH MỤC SẢN PHẨM</a>
            <div class="quick-links">
                <a class="<?= $isHome ? 'active' : '' ?>" href="<?= $basePath ?>/index.php">Trang chủ</a>
                <a class="<?= $isProducts ? 'active' : '' ?>" href="<?= $basePath ?>/views/products.php">Sản phẩm</a>
                <a href="<?= $basePath ?>/views/products.php?q=Laptop">Laptop</a>
                <a href="<?= $basePath ?>/views/products.php?q=Gaming">PC Gaming</a>
                <a href="<?= $basePath ?>/views/products.php?q=Phụ kiện">Phụ kiện</a>
                <a href="<?= $basePath ?>/views/products.php?sort=price_asc">Giá tốt</a>
            </div>
        </div>
    </nav>
</header>
<main>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash']['text'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
