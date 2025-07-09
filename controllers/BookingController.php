<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

class BookingController {
    private $booking;
    private $car;
    
    public function __construct() {
        $this->booking = new Booking();
        $this->car = new Car();
    }
    
    public function create() {
        require_login();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $car_id = (int)($_POST['car_id'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $pickup_location = sanitize_input($_POST['pickup_location'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');
        
        // Validation
        if (!$car_id || !$start_date || !$end_date) {
            error_response('Car ID, start date, and end date are required');
        }
        
        $car = $this->car->findById($car_id);
        if (!$car) {
            error_response('Car not found', 404);
        }
        
        // Check if car is available
        if (!$this->car->isAvailable($car_id, $start_date, $end_date)) {
            error_response('Car is not available for the selected dates');
        }
        
        // Calculate costs
        $days = calculate_days_between($start_date, $end_date);
        $total_amount = $days * $car['price_per_day'];
        
        $data = [
            'user_id' => $_SESSION['user_id'],
            'car_id' => $car_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_amount' => $total_amount,
            'security_deposit' => $car['security_deposit'],
            'pickup_location' => $pickup_location ?: $car['pickup_location'],
            'notes' => $notes
        ];
        
        $booking_id = $this->booking->create($data);
        
        if ($booking_id) {
            success_response([
                'booking_id' => $booking_id,
                'total_amount' => $total_amount,
                'days' => $days
            ], 'Booking created successfully');
        } else {
            error_response('Failed to create booking');
        }
    }
    
    public function getUserBookings() {
        require_login();
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = BOOKINGS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $bookings = $this->booking->getByUserId($_SESSION['user_id'], $limit, $offset);
        
        success_response(['bookings' => $bookings, 'page' => $page]);
    }
    
    public function getOwnerBookings() {
        require_login();
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = BOOKINGS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $bookings = $this->booking->getByCarOwnerId($_SESSION['user_id'], $limit, $offset);
        
        success_response(['bookings' => $bookings, 'page' => $page]);
    }
    
    public function getById($id) {
        require_login();
        
        $booking = $this->booking->findById($id);
        
        if (!$booking) {
            error_response('Booking not found', 404);
        }
        
        // Check if user has access to this booking
        $car = $this->car->findById($booking['car_id']);
        if ($booking['user_id'] != $_SESSION['user_id'] && $car['user_id'] != $_SESSION['user_id']) {
            error_response('Access denied', 403);
        }
        
        success_response(['booking' => $booking]);
    }
    
    public function updateStatus() {
        require_login();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? '');
        
        $valid_statuses = ['confirmed', 'cancelled', 'active', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            error_response('Invalid status');
        }
        
        $booking = $this->booking->findById($booking_id);
        if (!$booking) {
            error_response('Booking not found', 404);
        }
        
        // Check permissions
        $car = $this->car->findById($booking['car_id']);
        if ($booking['user_id'] != $_SESSION['user_id'] && $car['user_id'] != $_SESSION['user_id']) {
            error_response('Access denied', 403);
        }
        
        if ($this->booking->updateStatus($booking_id, $status)) {
            success_response([], 'Booking status updated successfully');
        } else {
            error_response('Failed to update booking status');
        }
    }
}
?>