<?php

declare(strict_types=1);

/**
 * Chuẩn hóa Session + CSRF cho toàn bộ dự án (SHOP-32, SHOP-33).
 * Mọi nơi cần session hoặc kiểm tra CSRF nên gọi qua class này,
 * thay vì tự viết lại session_start()/kiểm tra token riêng lẻ như trước.
 */
final class Security
{
    // Tự động đăng xuất nếu không hoạt động quá 30 phút
    private const INACTIVITY_TIMEOUT = 1800;

    /**
     * Khởi tạo session đúng 1 cách duy nhất trong toàn dự án.
     * An toàn khi gọi nhiều lần ở nhiều file khác nhau trong cùng 1 request
     * (không xảy ra lỗi "session already started").
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        self::enforceInactivityTimeout();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Nếu người dùng đã đăng nhập nhưng không hoạt động quá lâu,
     * hủy hoàn toàn session và chuyển về trang đăng nhập.
     */
    private static function enforceInactivityTimeout(): void
    {
        if (isset($_SESSION['user'])) {
            $lastActivity = $_SESSION['last_activity'] ?? time();
            if (time() - $lastActivity > self::INACTIVITY_TIMEOUT) {
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params['path'],
                        $params['domain'],
                        $params['secure'],
                        $params['httponly']
                    );
                }
                session_destroy();
                header(
                    'Location: index.php?action=login&error='
                    . urlencode('Phiên làm việc đã hết hạn do không hoạt động, vui lòng đăng nhập lại.')
                );
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * In ra input ẩn chứa CSRF token — dùng trong MỌI <form method="POST">.
     * Cách dùng trong view: <?= Security::csrfField() ?>
     */
    public static function csrfField(): string
    {
        self::startSession();

        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Kiểm tra CSRF token gửi lên từ form POST.
     * Dừng request với mã 419 nếu thiếu hoặc sai token.
     */
    public static function verifyCsrf(): void
    {
        self::startSession();

        $token = (string) ($_POST['csrf_token'] ?? '');
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            exit('Phiên làm việc đã hết hạn hoặc token không hợp lệ. Vui lòng tải lại trang.');
        }
    }

    /**
     * Chỉ cho phép phương thức POST — dùng cho mọi thao tác thêm/sửa/xoá.
     */
    public static function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }
    }

    /**
     * Chỉ giữ lại một tập thẻ định dạng an toàn cho trình soạn thảo mô tả.
     * Thuộc tính HTML bị loại bỏ để không thể chèn script hoặc event handler.
     */
    public static function sanitizeRichText(string $html): string
    {
        $allowedTags = '<p><div><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote>';
        $clean = strip_tags(trim($html), $allowedTags);
        $clean = preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/i', '<$1>', $clean) ?? '';

        return trim($clean);
    }

    /** Trả về HTML an toàn để hiển thị mô tả cũ dạng text hoặc nội dung editor. */
    public static function renderRichText(string $content): string
    {
        $clean = self::sanitizeRichText($content);
        if ($clean === strip_tags($clean)) {
            return nl2br(htmlspecialchars($clean, ENT_QUOTES, 'UTF-8'));
        }

        return $clean;
    }
}
