<?php
require_once __DIR__ . '/../config/config.php';

$controller = new CarController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'list':
        $controller->getAll();
        break;
    case 'details':
        $id = (int)($_GET['id'] ?? 0);
        $controller->getById($id);
        break;
    case 'user-cars':
        $controller->getUserCars();
        break;
    case 'check-availability':
        $controller->checkAvailability();
        break;
    default:
        error_response('Invalid action', 404);
}
?>