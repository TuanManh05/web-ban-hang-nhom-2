<?php

declare(strict_types=1);

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/../middleware/Security.php';

/**
 * Quản lý giỏ hàng lưu trong session.
 * Cấu trúc lưu trữ: $_SESSION['cart'] = [ product_id => quantity, ... ]
 */
final class Cart
{
    private const SESSION_KEY = 'cart';

    /** Đảm bảo session đã khởi động và key giỏ hàng tồn tại */
    private static function ensureSession(): void
    {
        Security::startSession();
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    private static function getRawCart(): array
    {
        self::ensureSession();
        return $_SESSION[self::SESSION_KEY];
    }

    private static function saveRawCart(array $cart): void
    {
        self::ensureSession();
        $_SESSION[self::SESSION_KEY] = $cart;
    }

    /**
     * Thêm sản phẩm vào giỏ. Nếu đã có, cộng dồn số lượng.
     * Số lượng cuối cùng không được vượt quá tồn kho.
     */
    public static function add(int $productId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);

        $product = Product::findById($productId);
        if (!$product || (int) $product['status'] !== 1) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.'];
        }

        $stock = (int) $product['stock'];
        if ($stock <= 0) {
            return ['success' => false, 'message' => 'Sản phẩm hiện đã hết hàng.'];
        }

        $cart = self::getRawCart();
        $current = (int) ($cart[$productId] ?? 0);
        $requested = $current + $quantity;
        $final = min($requested, $stock);

        $cart[$productId] = $final;
        self::saveRawCart($cart);

        if ($requested > $stock) {
            return [
                'success' => true,
                'message' => "Đã thêm vào giỏ, số lượng được giới hạn theo tồn kho hiện có ({$stock}).",
            ];
        }

        return ['success' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng.'];
    }

    /** Tăng số lượng một sản phẩm trong giỏ (không vượt quá tồn kho) */
    public static function increase(int $productId, int $step = 1): array
    {
        $cart = self::getRawCart();

        if (!isset($cart[$productId])) {
            return self::add($productId, $step);
        }

        $product = Product::findById($productId);
        if (!$product || (int) $product['status'] !== 1) {
            unset($cart[$productId]);
            self::saveRawCart($cart);
            return ['success' => false, 'message' => 'Sản phẩm không còn tồn tại và đã được gỡ khỏi giỏ hàng.'];
        }

        $stock = (int) $product['stock'];
        $newQty = (int) $cart[$productId] + $step;

        if ($newQty > $stock) {
            $cart[$productId] = $stock;
            self::saveRawCart($cart);
            return ['success' => false, 'message' => "Số lượng không thể vượt quá tồn kho ({$stock})."];
        }

        $cart[$productId] = $newQty;
        self::saveRawCart($cart);
        return ['success' => true, 'message' => 'Đã cập nhật số lượng.'];
    }

    /** Giảm số lượng một sản phẩm trong giỏ; nếu về 0 thì tự xoá */
    public static function decrease(int $productId, int $step = 1): array
    {
        $cart = self::getRawCart();

        if (!isset($cart[$productId])) {
            return ['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.'];
        }

        $newQty = (int) $cart[$productId] - $step;

        if ($newQty <= 0) {
            unset($cart[$productId]);
            self::saveRawCart($cart);
            return ['success' => true, 'message' => 'Đã xoá sản phẩm khỏi giỏ hàng.'];
        }

        $cart[$productId] = $newQty;
        self::saveRawCart($cart);
        return ['success' => true, 'message' => 'Đã cập nhật số lượng.'];
    }

    /** Xoá một sản phẩm khỏi giỏ hàng */
    public static function remove(int $productId): void
    {
        $cart = self::getRawCart();
        unset($cart[$productId]);
        self::saveRawCart($cart);
    }

    /** Xoá toàn bộ giỏ hàng */
    public static function clear(): void
    {
        self::saveRawCart([]);
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ kèm thông tin chi tiết (tên, giá, ảnh, tồn kho).
     * Tự động dọn dẹp sản phẩm đã bị xoá/ngừng bán hoặc vượt tồn kho.
     */
    public static function getItems(): array
    {
        $cart = self::getRawCart();
        $result = [];
        $changed = false;

        foreach ($cart as $productId => $quantity) {
            $product = Product::findById((int) $productId);

            if (!$product || (int) $product['status'] !== 1) {
                unset($cart[$productId]);
                $changed = true;
                continue;
            }

            $stock = (int) $product['stock'];
            $quantity = (int) $quantity;

            if ($stock <= 0) {
                unset($cart[$productId]);
                $changed = true;
                continue;
            }

            if ($quantity > $stock) {
                $quantity = $stock;
                $cart[$productId] = $quantity;
                $changed = true;
            }

            $price = (float) $product['price'];

            $result[] = [
                'product_id' => (int) $product['id'],
                'name'       => $product['name'],
                'image_path' => $product['image_path'] ?? null,
                'price'      => $price,
                'stock'      => $stock,
                'quantity'   => $quantity,
                'subtotal'   => $price * $quantity,
            ];
        }

        if ($changed) {
            self::saveRawCart($cart);
        }

        return $result;
    }

    /** Tổng số lượng sản phẩm trong giỏ (dùng cho badge trên header) */
    public static function getTotalQuantity(): int
    {
        $cart = self::getRawCart();
        return array_sum(array_map('intval', $cart));
    }

    /** Tổng tiền dự kiến của giỏ hàng */
    public static function getTotalAmount(): float
    {
        $total = 0.0;
        foreach (self::getItems() as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }
}
