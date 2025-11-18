<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../shared/helpers/auth_helper.php';

// basic routing: use ?page=path (e.g. ?page=auth/login)
$page = $_GET['page'] ?? 'auth/login';
$parts = explode('/', $page);
$feature = $parts[0];
$action = $parts[1] ?? 'index';

$controller_file = __DIR__ . '/../features/' . $feature . '/controllers/' . ucfirst($feature) . 'Controller.php';

if (file_exists($controller_file)) {
    require_once $controller_file;
    $controllerClass = ucfirst($feature) . 'Controller';
    if (class_exists($controllerClass)) {
        $c = new $controllerClass();
        if (method_exists($c, $action)) {
            $c->{$action}();
            exit;
        }
    }
}

// fallback: show simple message
echo "<h1>Page not found: " . htmlspecialchars($page) . "</h1>";
