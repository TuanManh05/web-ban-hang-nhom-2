<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="index.php?action=admin" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại Admin</a>
        <h2 class="h3 mb-0 text-gray-800">Danh sách danh mục</h2>
        <!-- Nút thêm danh mục mới -->
        <a href="index.php?action=category-create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm danh mục mới
        </a>
    </div>

    <!-- Thông báo thành công (Flash Message) -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Thông báo lỗi -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 60px;">ID</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center" style="width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $cat['id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($cat['name']) ?></div>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($cat['slug']) ?></code>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cat['status'] == 1): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tạm ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Nút Sửa -->
                                            <a href="index.php?action=category-edit&id=<?= $cat['id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>
                                            <form action="index.php?action=category-delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này không?');">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có danh mục nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
