<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm danh mục mới</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-4">
<div class="container" style="max-width: 600px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Thêm danh mục mới</h2>
        <a href="index.php?action=admin" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại Admin</a>
    </div>

    <!-- Hiển thị danh sách lỗi nếu Validation thất bại -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="index.php?action=category-store" method="POST">
                <?= Security::csrfField() ?>
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Nhập tên danh mục (VD: Bánh ngọt, Bánh kem...)" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1" <?= (isset($_POST['status']) && $_POST['status'] == '1') ? 'selected' : '' ?>>Hoạt động (Hiển thị)</option>
                        <option value="0" <?= (isset($_POST['status']) && $_POST['status'] == '0') ? 'selected' : '' ?>>Tạm ẩn</option>
                    </select>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php?action=category-index" class="btn btn-light me-md-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4">Lưu danh mục</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
