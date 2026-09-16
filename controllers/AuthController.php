<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/Security.php';

final class AuthController
{
    private User $users;

    public function __construct(PDO $pdo)
    {
        Security::startSession();

        $this->users = new User($pdo);
    }

    public function showRegister(): void
    {
        AuthMiddleware::redirectAuthenticatedUser();
        $pageTitle = 'Đăng ký';
        $errors = [];
        $old = ['name' => '', 'email' => ''];
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(): void
    {
        AuthMiddleware::redirectAuthenticatedUser();
        Security::requirePost();
        Security::verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $old = ['name' => $name, 'email' => $email];
        $errors = [];

        if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors[] = 'Họ tên phải có từ 2 đến 100 ký tự.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Email không hợp lệ.';
        } elseif ($this->users->emailExists($email)) {
            $errors[] = 'Email đã được sử dụng.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Xác nhận mật khẩu không khớp.';
        }

        if ($errors !== []) {
            $pageTitle = 'Đăng ký';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        $this->users->create($name, $email, $password);
        header('Location: index.php?action=login&msg=' . urlencode('Đăng ký thành công. Bạn có thể đăng nhập.'));
        exit;
    }

    public function showLogin(): void
    {
        AuthMiddleware::redirectAuthenticatedUser();
        $pageTitle = 'Đăng nhập';
        $error = (string) ($_GET['error'] ?? '');
        $message = (string) ($_GET['msg'] ?? '');
        $email = '';
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        AuthMiddleware::redirectAuthenticatedUser();
        Security::requirePost();
        Security::verifyCsrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users->findByEmail($email) : null;

        if (!$user || (int) $user['status'] !== 1 || !password_verify($password, $user['password'])) {
            $pageTitle = 'Đăng nhập';
            $error = 'Email hoặc mật khẩu không đúng.';
            $message = '';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        $_SESSION['last_activity'] = time();

        $action = $user['role'] === 'admin' ? 'admin' : 'home';
        header('Location: index.php?action=' . $action);
        exit;
    }

    public function logout(): void
    {
        Security::requirePost();
        Security::verifyCsrf();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        header('Location: index.php?action=login&msg=' . urlencode('Bạn đã đăng xuất.'));
        exit;
    }
}
