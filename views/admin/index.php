<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../../index.php?action=login&error=' . urlencode('Bạn không có quyền truy cập trang quản trị.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Admin Dashboard</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<!-- Navbar Dashboard Admin -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php?action=admin">
            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <div class="ms-auto d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['user'])): ?>
                    <span class="navbar-text text-white me-2">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Admin') ?>
                    </span>
                <?php endif; ?>
                <a href="index.php?action=home" class="btn btn-outline-light btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Xem trang chủ
                </a>
                <form method="post" action="index.php?action=logout" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="container py-2">
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-4">
        <h2 class="h3 fw-bold text-gray-800">Bảng Quản Trị Hệ Thống</h2>
        <p class="text-muted">Lựa chọn các chức năng quản lý bên dưới để bắt đầu công việc.</p>
    </div>

    <!-- các thẻ điều hướng chính -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý Sản phẩm</h5>
                    <p class="card-text text-muted small">Xem danh sách, thêm mới, cập nhật giá và tồn kho.</p>
                    <a href="index.php?action=product-index" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-arrow-right-circle me-1"></i>Truy cập Sản phẩm
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-tags fs-3"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý Danh mục</h5>
                    <p class="card-text text-muted small">Phân loại sản phẩm và quản lý danh mục bánh.</p>
                    <a href="index.php?action=category-index" class="btn btn-success w-100 mt-2">
                        <i class="bi bi-arrow-right-circle me-1"></i>Truy cập Danh mục
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý Đơn hàng</h5>
                    <p class="card-text text-muted small">Xem chi tiết và cập nhật trạng thái đơn hàng.</p>
                    <a href="index.php?action=admin-orders" class="btn btn-warning w-100 mt-2">Truy cập Đơn hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px">
                        <i class="bi bi-bar-chart-line fs-3"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thống kê</h5>
                    <p class="card-text text-muted small">Doanh thu, đơn hàng theo trạng thái, sản phẩm bán chạy.</p>
                    <a href="index.php?action=admin-stats" class="btn btn-info w-100 mt-2 text-white">
                        <i class="bi bi-arrow-right-circle me-1"></i>Xem thống kê
                    </a>
                </div>
            </div>
        </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
