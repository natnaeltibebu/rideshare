<?php
require_once __DIR__ . '/../config/config.php';

$controller = new AdminController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dashboard-stats':
        $controller->getDashboardStats();
        break;
    case 'cars':
        $controller->getCars();
        break;
    case 'car-details':
        $controller->getCarDetails();
        break;
    case 'update-car':
        $controller->updateCar();
        break;
    case 'delete-car':
        $controller->deleteCar();
        break;
    case 'users':
        $controller->getUsers();
        break;
    case 'user-details':
        $controller->getUserDetails();
        break;
    case 'update-user':
        $controller->updateUser();
        break;
    case 'add-user':
        $controller->addUser();
        break;
    case 'toggle-user-status':
        $controller->toggleUserStatus();
        break;
    case 'bookings':
        $controller->getBookings();
        break;
    case 'booking-details':
        $controller->getBookingDetails();
        break;
    case 'update-booking-status':
        $controller->updateBookingStatus();
        break;
    case 'recent-activity':
        $controller->getRecentActivity();
        break;
    default:
        error_response('Invalid action', 404);
}
?>