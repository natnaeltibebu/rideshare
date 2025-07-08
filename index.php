<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Simple routing
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/rideshare', '', $request_uri);

switch ($request_uri) {
    case '/':
    case '/home':
        include __DIR__ . '/pages/home.php';
        break;
    case '/login':
        include __DIR__ . '/pages/login.php';
        break;
    case '/browse':
        include __DIR__ . '/pages/browse.php';
        break;
    case '/details':
        include __DIR__ . '/pages/details.php';
        break;
    case '/host':
        if (is_logged_in()) {
            include __DIR__ . '/pages/host.php';
        } else {
            include __DIR__ . '/pages/login.php';
        }
        break;
    case '/dashboard':
        require_login();
        include __DIR__ . '/pages/dashboard.php';
        break;
    case '/admin':
        require_once __DIR__ . '/includes/admin_auth.php';
        require_admin_or_host();
        include __DIR__ . '/pages/admin.php';
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
?>