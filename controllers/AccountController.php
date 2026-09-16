<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

final class AccountController
{
    private User $users;

    public function __construct(PDO $pdo)
    {
        $this->users = new User($pdo);
    }

    public function profile(): void
    {
        AuthMiddleware::requireLogin();
        $user = $this->users->findById((int) $_SESSION['user']['id']);
        $pageTitle = 'Thông tin cá nhân';
        require __DIR__ . '/../views/account/profile.php';
    }

    public function updateProfile(): void
    {
        AuthMiddleware::requireLogin();
        Security::requirePost();
        Security::verifyCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            header('Location: index.php?action=profile&error=' . urlencode('Họ tên phải có từ 2 đến 100 ký tự.'));
            exit;
        }
        $this->users->updateName((int) $_SESSION['user']['id'], $name);
        $_SESSION['user']['name'] = $name;
        header('Location: index.php?action=profile&msg=' . urlencode('Đã cập nhật thông tin cá nhân.'));
        exit;
    }

    public function changePassword(): void
    {
        AuthMiddleware::requireLogin();
        Security::requirePost();
        Security::verifyCsrf();
        $user = $this->users->findById((int) $_SESSION['user']['id']);
        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        if (!$user || !password_verify($current, (string) $user['password'])) { $error = 'Mật khẩu hiện tại không đúng.'; }
        elseif (strlen($password) < 8) { $error = 'Mật khẩu mới phải có ít nhất 8 ký tự.'; }
        elseif ($password !== $confirmation) { $error = 'Xác nhận mật khẩu mới không khớp.'; }
        elseif (password_verify($password, (string) $user['password'])) { $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.'; }
        if (isset($error)) { header('Location: index.php?action=profile&error=' . urlencode($error)); exit; }
        $this->users->updatePassword((int) $user['id'], $password);
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php?action=profile&msg=' . urlencode('Đổi mật khẩu thành công.'));
        exit;
    }
}
