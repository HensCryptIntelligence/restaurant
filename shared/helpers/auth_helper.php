<?php
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /?page=auth/login');
        exit;
    }
}

function current_user() {
    if (!is_logged_in()) return null;
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, username, email, role, fullname FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch();
}
