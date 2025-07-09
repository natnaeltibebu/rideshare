<?php
require_once __DIR__ . '/../config/config.php';

$controller = new BookingController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'user-bookings':
        $controller->getUserBookings();
        break;
    case 'owner-bookings':
        $controller->getOwnerBookings();
        break;
    case 'details':
        $id = (int)($_GET['id'] ?? 0);
        $controller->getById($id);
        break;
    case 'update-status':
        $controller->updateStatus();
        break;
    default:
        error_response('Invalid action', 404);
}
?>