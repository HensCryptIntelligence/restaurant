<?php
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../shared/helpers/csrf.php';

class AuthController {
    public function index() { $this->login(); }

    public function login() {
        require_once BASE_PATH . '/shared/partials/header.php';
        require_once BASE_PATH . '/features/auth/views/login.php';
        require_once BASE_PATH . '/shared/partials/footer.php';
    }

    public function register() {
        require_once BASE_PATH . '/shared/partials/header.php';
        require_once BASE_PATH . '/features/auth/views/register.php';
        require_once BASE_PATH . '/shared/partials/footer.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /?page=auth/login');
        exit;
    }

    public function do_register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?page=auth/register'); exit; }
        if (!validate_csrf($_POST['_csrf'] ?? '')) { die('CSRF token invalid'); }
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$username || !$email || !$password) { die('Missing fields'); }

        $user = new User();
        $exists = $user->findByUsernameOrEmail($username, $email);
        if ($exists) { die('User already exists'); }

        $id = $user->create($username, $email, $password);
        if ($id) {
            $_SESSION['user_id'] = $id;
            header('Location: /?page=customer/home');
            exit;
        }
        die('Registration failed');
    }

    public function do_login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?page=auth/login'); exit; }
        if (!validate_csrf($_POST['_csrf'] ?? '')) { die('CSRF token invalid'); }
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = new User();
        $u = $user->findByUsernameOrEmail($username, $username);
        if (!$u) { die('User not found'); }
        if (!password_verify($password, $u['password_hash'])) { die('Invalid password'); }
        $_SESSION['user_id'] = $u['id'];
        header('Location: /?page=customer/home');
        exit;
    }
}
