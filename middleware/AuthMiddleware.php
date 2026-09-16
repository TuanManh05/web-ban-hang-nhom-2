<?php

declare(strict_types=1);

require_once __DIR__ . '/Security.php';

final class AuthMiddleware
{
    public static function requireLogin(): void
    {
        Security::startSession();

        if (!isset($_SESSION['user'])) {
            self::redirectToLogin('Vui lòng đăng nhập để tiếp tục.');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=home&error=' . urlencode('Bạn không có quyền truy cập trang quản trị.'));
            exit;
        }
    }

    public static function redirectAuthenticatedUser(): void
    {
        Security::startSession();

        if (isset($_SESSION['user'])) {
            $action = ($_SESSION['user']['role'] ?? '') === 'admin' ? 'admin' : 'home';
            header('Location: index.php?action=' . $action);
            exit;
        }
    }

    private static function redirectToLogin(string $message): never
    {
        header('Location: index.php?action=login&error=' . urlencode($message));
        exit;
    }
}
