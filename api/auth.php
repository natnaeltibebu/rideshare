<?php
require_once __DIR__ . '/../config/config.php';

$controller = new AuthController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'profile':
        $controller->getProfile();
        break;
    default:
        error_response('Invalid action', 404);
}
?>