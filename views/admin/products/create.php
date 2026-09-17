<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin-editor.css">
</head>
<body class="bg-light py-4">
<div class="container" style="max-width: 700px;">
    <!-- Header có nút Quay lại Admin -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Thêm sản phẩm mới</h2>
        <a href="index.php?action=admin" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại Admin
        </a>
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
            <!-- Form gửi về action product-store -->
            <form action="index.php?action=product-store" method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Nhập tên sản phẩm" required>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label fw-bold">Giá sản phẩm (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" placeholder="VD: 150000" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="stock" class="form-label fw-bold">Số lượng (Stock) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>" min="0" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descriptionEditor" class="form-label fw-bold">Mô tả sản phẩm</label>
                    <div class="rich-editor-toolbar" data-editor-toolbar="descriptionEditor" aria-label="Công cụ định dạng mô tả">
                        <button type="button" data-command="bold" title="In đậm"><strong>B</strong></button>
                        <button type="button" data-command="italic" title="In nghiêng"><em>I</em></button>
                        <button type="button" data-command="underline" title="Gạch chân"><u>U</u></button>
                        <button type="button" data-command="insertUnorderedList" title="Danh sách">• Danh sách</button>
                        <button type="button" data-command="formatBlock" data-value="h3" title="Tiêu đề">H3</button>
                        <button type="button" data-command="removeFormat" title="Xóa định dạng">Xóa định dạng</button>
                    </div>
                    <div id="descriptionEditor" class="form-control rich-text-editor" contenteditable="true" role="textbox" aria-multiline="true" data-editor-for="description"><?= Security::renderRichText((string) ($_POST['description'] ?? '')) ?></div>
                    <textarea class="d-none" id="description" name="description"><?= htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text">Có thể định dạng tiêu đề, chữ đậm, chữ nghiêng và danh sách.</div>
                </div>

                <div class="mb-3">
                    <label for="images" class="form-label fw-bold">Hình ảnh sản phẩm</label>
                    <input class="form-control" type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-image-input data-preview-target="imagePreview">
                    <div class="form-text">Chọn tối đa 5 ảnh JPG, PNG hoặc WEBP; mỗi ảnh không quá 5 MB. Ảnh đầu tiên sẽ là ảnh đại diện.</div>
                    <div id="imagePreview" class="image-upload-preview mt-3" aria-live="polite"></div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1" <?= (isset($_POST['status']) && $_POST['status'] == '1') ? 'selected' : '' ?>>Đang bán (Hiển thị)</option>
                        <option value="0" <?= (isset($_POST['status']) && $_POST['status'] == '0') ? 'selected' : '' ?>>Ẩn / Tạm ngưng</option>
                    </select>
                </div>

                <!-- Khu vực nút bấm cuối form: Gom lại còn duy nhất 1 nút Hủy bỏ -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php?action=product-index" class="btn btn-light me-md-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4">Lưu sản phẩm</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/rich-text-editor.js"></script>
<script src="assets/js/image-upload-preview.js"></script>
</body>
</html>
