<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-4">
<div class="container" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Chỉnh sửa sản phẩm #<?= $product['id'] ?></h2>
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
            <form action="index.php?action=product-update&id=<?= $product['id'] ?>" method="POST">
                <?= Security::csrfField() ?>
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-bold">Slug hiện tại</label>
                    <input type="text" class="form-control bg-light" id="slug" value="<?= htmlspecialchars($product['slug']) ?>" readonly disabled>
                    <small class="text-muted">Slug sẽ tự động cập nhật nếu bạn đổi Tên sản phẩm.</small>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php 
                            $selectedCategory = $_POST['category_id'] ?? $product['category_id'];
                            foreach ($categories as $cat): 
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label fw-bold">Giá sản phẩm (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="stock" class="form-label fw-bold">Số lượng (Stock) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>" min="0" required>
                    </div>
                </div>

                <!-- Thêm trường Mô tả sản phẩm (Description) -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Mô tả sản phẩm</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Nhập chi tiết mô tả sản phẩm..."><?= htmlspecialchars($_POST['description'] ?? $product['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Trạng thái</label>
                    <?php $currentStatus = $_POST['status'] ?? $product['status']; ?>
                    <select class="form-select" id="status" name="status">
                        <option value="1" <?= $currentStatus == 1 ? 'selected' : '' ?>>Đang bán (Hiển thị)</option>
                        <option value="0" <?= $currentStatus == 0 ? 'selected' : '' ?>>Ẩn / Tạm ngưng</option>
                    </select>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php?action=product-index" class="btn btn-light me-md-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4">Cập nhật sản phẩm</button>
                </div>

            </form>
        </div>
    </div>
</div>
</body>
</html>
