<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

class AdminController {
    private $db;
    private $user;
    private $car;
    private $booking;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User();
        $this->car = new Car();
        $this->booking = new Booking();
    }
    
    public function getDashboardStats() {
        require_admin_or_host();
        
        try {
            $user_role = get_user_role();
            
            if ($user_role === 'admin') {
                // Admin sees all stats
                $stats = $this->getAllStats();
            } else {
                // Host sees only their stats
                $stats = $this->getHostStats($_SESSION['user_id']);
            }
            
            success_response($stats);
        } catch (Exception $e) {
            error_response('Failed to fetch dashboard stats: ' . $e->getMessage());
        }
    }
    
    private function getAllStats() {
        // Total cars
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cars");
        $stmt->execute();
        $total_cars = $stmt->fetch()['total'];
        
        // Active users
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE is_verified = 1");
        $stmt->execute();
        $active_users = $stmt->fetch()['total'];
        
        // Total bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings");
        $stmt->execute();
        $total_bookings = $stmt->fetch()['total'];
        
        // Revenue calculation
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as revenue FROM bookings WHERE status IN ('completed', 'active')");
        $stmt->execute();
        $total_revenue = $stmt->fetch()['revenue'] ?? 0;
        
        // Growth calculations (compared to last month)
        $last_month = date('Y-m-d', strtotime('-1 month'));
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cars WHERE created_at >= ?");
        $stmt->execute([$last_month]);
        $cars_growth = $stmt->fetch()['total'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE created_at >= ?");
        $stmt->execute([$last_month]);
        $users_growth = $stmt->fetch()['total'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings WHERE created_at >= ?");
        $stmt->execute([$last_month]);
        $bookings_growth = $stmt->fetch()['total'];
        
        return [
            'total_cars' => $total_cars,
            'cars_growth' => $cars_growth,
            'active_users' => $active_users,
            'users_growth' => $users_growth,
            'total_bookings' => $total_bookings,
            'bookings_growth' => $bookings_growth,
            'total_revenue' => $total_revenue
        ];
    }
    
    private function getHostStats($user_id) {
        // Host's cars
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cars WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_cars = $stmt->fetch()['total'];
        
        // Host's bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings b JOIN cars c ON b.car_id = c.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $total_bookings = $stmt->fetch()['total'];
        
        // Host's revenue
        $stmt = $this->db->prepare("SELECT SUM(b.total_amount) as revenue FROM bookings b JOIN cars c ON b.car_id = c.id WHERE c.user_id = ? AND b.status IN ('completed', 'active')");
        $stmt->execute([$user_id]);
        $total_revenue = $stmt->fetch()['revenue'] ?? 0;
        
        return [
            'total_cars' => $total_cars,
            'total_bookings' => $total_bookings,
            'total_revenue' => $total_revenue,
            'avg_rating' => 4.8 // You can calculate this from reviews
        ];
    }
    
    public function getCars() {
        require_admin_or_host();
        
        $user_role = get_user_role();
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            if ($user_role === 'admin') {
                // Admin sees all cars
                $query = "SELECT c.*, u.first_name, u.last_name, u.email,
                         (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
                         FROM cars c 
                         JOIN users u ON c.user_id = u.id";
                $count_query = "SELECT COUNT(*) as total FROM cars c";
                $params = [];
            } else {
                // Host sees only their cars
                $query = "SELECT c.*, u.first_name, u.last_name, u.email,
                         (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
                         FROM cars c 
                         JOIN users u ON c.user_id = u.id 
                         WHERE c.user_id = ?";
                $count_query = "SELECT COUNT(*) as total FROM cars c WHERE c.user_id = ?";
                $params = [$_SESSION['user_id']];
            }
            
            if ($search) {
                $search_condition = " AND (c.make LIKE ? OR c.model LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $query .= $search_condition;
                $count_query .= ($user_role === 'admin' ? " WHERE 1=1" : "") . $search_condition;
                
                $search_term = "%$search%";
                $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            }
            
            $query .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $cars = $stmt->fetchAll();
            
            // Get total count
            $count_params = array_slice($params, 0, -2); // Remove limit and offset
            $stmt = $this->db->prepare($count_query);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            success_response([
                'cars' => $cars,
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_response('Failed to fetch cars: ' . $e->getMessage());
        }
    }

    public function getCarDetails() {
        require_admin_or_host();
        
        $car_id = (int)($_GET['id'] ?? 0);
        $user_role = get_user_role();
        
        try {
            $query = "SELECT c.* FROM cars c WHERE c.id = ?";
            $params = [$car_id];
            
            // Host can only view their own cars
            if ($user_role === 'host') {
                $query .= " AND c.user_id = ?";
                $params[] = $_SESSION['user_id'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $car = $stmt->fetch();
            
            if (!$car) {
                error_response('Car not found', 404);
            }
            
            success_response($car);
        } catch (Exception $e) {
            error_response('Failed to fetch car details: ' . $e->getMessage());
        }
    }
    
    public function updateCar() {
        require_admin_or_host();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $car_id = (int)($_POST['car_id'] ?? 0);
        $user_role = get_user_role();
        
        // Check if user has permission to update this car
        if ($user_role === 'host') {
            $stmt = $this->db->prepare("SELECT user_id FROM cars WHERE id = ?");
            $stmt->execute([$car_id]);
            $car = $stmt->fetch();
            
            if (!$car || $car['user_id'] != $_SESSION['user_id']) {
                error_response('Access denied', 403);
            }
        }
        
        $data = [
            'make' => sanitize_input($_POST['make'] ?? ''),
            'model' => sanitize_input($_POST['model'] ?? ''),
            'year' => (int)($_POST['year'] ?? 0),
            'vehicle_type' => sanitize_input($_POST['vehicle_type'] ?? ''),
            'transmission' => sanitize_input($_POST['transmission'] ?? ''),
            'fuel_type' => sanitize_input($_POST['fuel_type'] ?? ''),
            'seating_capacity' => (int)($_POST['seating_capacity'] ?? 0),
            'price_per_day' => (float)($_POST['price_per_day'] ?? 0),
            'security_deposit' => (float)($_POST['security_deposit'] ?? 0),
            'pickup_location' => sanitize_input($_POST['pickup_location'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'available_from' => $_POST['available_from'] ?? '',
            'available_to' => $_POST['available_to'] ?? '',
            'status' => sanitize_input($_POST['status'] ?? 'active')
        ];
        
        // Validation
        $valid_vehicle_types = ['sedan', 'suv', 'hatchback', 'convertible', 'coupe', 'truck', 'van'];
        $valid_transmissions = ['manual', 'automatic', 'cvt'];
        $valid_fuel_types = ['petrol', 'diesel', 'electric', 'hybrid'];
        $valid_statuses = ['active', 'inactive', 'maintenance'];
        
        if (!in_array($data['vehicle_type'], $valid_vehicle_types)) {
            error_response('Invalid vehicle type');
        }
        
        if (!in_array($data['transmission'], $valid_transmissions)) {
            error_response('Invalid transmission type');
        }
        
        if (!in_array($data['fuel_type'], $valid_fuel_types)) {
            error_response('Invalid fuel type');
        }
        
        if (!in_array($data['status'], $valid_statuses)) {
            error_response('Invalid status');
        }
        
        if ($data['year'] < 1900 || $data['year'] > 2030) {
            error_response('Invalid year');
        }
        
        if ($data['seating_capacity'] < 2 || $data['seating_capacity'] > 8) {
            error_response('Invalid seating capacity');
        }
        
        if ($data['price_per_day'] <= 0) {
            error_response('Price per day must be greater than 0');
        }
        
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
            
            $params[] = $car_id;
            
            $query = "UPDATE cars SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                success_response([], 'Car updated successfully');
            } else {
                error_response('Failed to update car');
            }
        } catch (Exception $e) {
            error_response('Failed to update car: ' . $e->getMessage());
        }
    }
    
    public function deleteCar() {
        require_admin_or_host();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $car_id = (int)($_POST['car_id'] ?? 0);
        $user_role = get_user_role();
        
        try {
            // Check if user has permission to delete this car
            if ($user_role === 'host') {
                $stmt = $this->db->prepare("SELECT user_id FROM cars WHERE id = ?");
                $stmt->execute([$car_id]);
                $car = $stmt->fetch();
                
                if (!$car || $car['user_id'] != $_SESSION['user_id']) {
                    error_response('Access denied', 403);
                }
            }
            
            // Check if car has active bookings
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE car_id = ? AND status IN ('confirmed', 'active')");
            $stmt->execute([$car_id]);
            if ($stmt->fetch()['count'] > 0) {
                error_response('Cannot delete car with active bookings');
            }
            
            // Delete car images first
            $stmt = $this->db->prepare("DELETE FROM car_images WHERE car_id = ?");
            $stmt->execute([$car_id]);
            
            // Delete the car
            $stmt = $this->db->prepare("DELETE FROM cars WHERE id = ?");
            $result = $stmt->execute([$car_id]);
            
            if ($result) {
                success_response([], 'Car deleted successfully');
            } else {
                error_response('Failed to delete car');
            }
        } catch (Exception $e) {
            error_response('Failed to delete car: ' . $e->getMessage());
        }
    }
    
    public function getUsers() {
        require_admin();
        
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            $query = "SELECT id, first_name, last_name, email, phone, role, is_verified, created_at FROM users";
            $count_query = "SELECT COUNT(*) as total FROM users";
            $params = [];
            
            if ($search) {
                $search_condition = " WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
                $query .= $search_condition;
                $count_query .= $search_condition;
                
                $search_term = "%$search%";
                $params = [$search_term, $search_term, $search_term];
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            // Get total count
            $count_params = array_slice($params, 0, -2);
            $stmt = $this->db->prepare($count_query);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            success_response([
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_response('Failed to fetch users: ' . $e->getMessage());
        }
    }
    
    public function getUserDetails() {
        require_admin();
        
        $user_id = (int)($_GET['id'] ?? 0);
        
        try {
            $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, phone, role, is_verified FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                error_response('User not found', 404);
            }
            
            success_response($user);
        } catch (Exception $e) {
            error_response('Failed to fetch user details: ' . $e->getMessage());
        }
    }
    
    public function updateUser() {
        require_admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $user_id = (int)($_POST['user_id'] ?? 0);
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $role = sanitize_input($_POST['role'] ?? '');
        
        $valid_roles = ['renter', 'host', 'admin'];
        if (!in_array($role, $valid_roles)) {
            error_response('Invalid role');
        }
        
        try {
            // Check if email is already taken by another user
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                error_response('Email is already taken');
            }
            
            $stmt = $this->db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
            $result = $stmt->execute([$first_name, $last_name, $email, $phone, $role, $user_id]);
            
            if ($result) {
                success_response([], 'User updated successfully');
            } else {
                error_response('Failed to update user');
            }
        } catch (Exception $e) {
            error_response('Failed to update user: ' . $e->getMessage());
        }
    }
    
    public function addUser() {
        require_admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize_input($_POST['role'] ?? '');
        
        $valid_roles = ['renter', 'host', 'admin'];
        if (!in_array($role, $valid_roles)) {
            error_response('Invalid role');
        }
        
        if (strlen($password) < 6) {
            error_response('Password must be at least 6 characters');
        }
        
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                error_response('Email is already taken');
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $result = $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password, $role]);
            
            if ($result) {
                success_response([], 'User added successfully');
            } else {
                error_response('Failed to add user');
            }
        } catch (Exception $e) {
            error_response('Failed to add user: ' . $e->getMessage());
        }
    }
    
    public function toggleUserStatus() {
        require_admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $user_id = (int)($_POST['user_id'] ?? 0);
        $is_verified = (int)($_POST['is_verified'] ?? 0);
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_verified = ? WHERE id = ?");
            $result = $stmt->execute([$is_verified, $user_id]);
            
            if ($result) {
                $action = $is_verified ? 'activated' : 'suspended';
                success_response([], "User $action successfully");
            } else {
                error_response('Failed to update user status');
            }
        } catch (Exception $e) {
            error_response('Failed to update user status: ' . $e->getMessage());
        }
    }
    
    public function getBookings() {
        require_admin_or_host();
        
        $user_role = get_user_role();
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = $_GET['limit'] ?? 10;
        $offset = ($page - 1) * $limit;
        
        try {
            if ($user_role === 'admin') {
                $query = "SELECT b.*, c.make, c.model, c.year, u.first_name, u.last_name, u.email, u.phone,
                         (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                         FROM bookings b 
                         JOIN cars c ON b.car_id = c.id 
                         JOIN users u ON b.user_id = u.id";
                $count_query = "SELECT COUNT(*) as total FROM bookings b JOIN cars c ON b.car_id = c.id JOIN users u ON b.user_id = u.id";
                $params = [];
            } else {
                $query = "SELECT b.*, c.make, c.model, c.year, u.first_name, u.last_name, u.email, u.phone,
                         (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                         FROM bookings b 
                         JOIN cars c ON b.car_id = c.id 
                         JOIN users u ON b.user_id = u.id 
                         WHERE c.user_id = ?";
                $count_query = "SELECT COUNT(*) as total FROM bookings b JOIN cars c ON b.car_id = c.id WHERE c.user_id = ?";
                $params = [$_SESSION['user_id']];
            }
            
            $conditions = [];
            if ($search) {
                $conditions[] = "(c.make LIKE ? OR c.model LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $search_term = "%$search%";
                $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            }
            
            if ($status) {
                $conditions[] = "b.status = ?";
                $params[] = $status;
            }
            
            if ($conditions) {
                $condition_str = implode(' AND ', $conditions);
                $query .= ($user_role === 'admin' ? " WHERE " : " AND ") . $condition_str;
                $count_query .= ($user_role === 'admin' ? " WHERE " : " AND ") . $condition_str;
            }
            
            $query .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll();
            
            // Get total count
            $count_params = array_slice($params, 0, -2);
            $stmt = $this->db->prepare($count_query);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            success_response([
                'bookings' => $bookings,
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_response('Failed to fetch bookings: ' . $e->getMessage());
        }
    }
    
    public function getBookingDetails() {
        require_admin_or_host();
        
        $booking_id = (int)($_GET['id'] ?? 0);
        $user_role = get_user_role();
        
        try {
            $query = "SELECT b.*, c.make, c.model, c.year, u.first_name, u.last_name, u.email, u.phone,
                     (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                     FROM bookings b 
                     JOIN cars c ON b.car_id = c.id 
                     JOIN users u ON b.user_id = u.id 
                     WHERE b.id = ?";
            
            $params = [$booking_id];
            
            // Host can only view bookings for their cars
            if ($user_role === 'host') {
                $query .= " AND c.user_id = ?";
                $params[] = $_SESSION['user_id'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $booking = $stmt->fetch();
            
            if (!$booking) {
                error_response('Booking not found', 404);
            }
            
            success_response($booking);
        } catch (Exception $e) {
            error_response('Failed to fetch booking details: ' . $e->getMessage());
        }
    }
    
    public function updateBookingStatus() {
        require_admin_or_host();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? '');
        
        $valid_statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            error_response('Invalid status');
        }
        
        try {
            // Check if user has permission to update this booking
            $user_role = get_user_role();
            if ($user_role === 'host') {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.id = ? AND c.user_id = ?");
                $stmt->execute([$booking_id, $_SESSION['user_id']]);
                if ($stmt->fetch()['count'] == 0) {
                    error_response('Access denied', 403);
                }
            }
            
            $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $result = $stmt->execute([$status, $booking_id]);
            
            if ($result) {
                success_response([], 'Booking status updated successfully');
            } else {
                error_response('Failed to update booking status');
            }
        } catch (Exception $e) {
            error_response('Failed to update booking status: ' . $e->getMessage());
        }
    }
    
    public function getRecentActivity() {
        require_admin_or_host();
        
        $user_role = get_user_role();
        $limit = (int)($_GET['limit'] ?? 10);
        
        try {
            if ($user_role === 'admin') {
                $query = "
                    (SELECT 'user_registered' as type, u.first_name, u.last_name, u.created_at as timestamp, NULL as car_info
                     FROM users u WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    UNION ALL
                    (SELECT 'car_listed' as type, u.first_name, u.last_name, c.created_at as timestamp, CONCAT(c.make, ' ', c.model) as car_info
                     FROM cars c JOIN users u ON c.user_id = u.id WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    UNION ALL
                    (SELECT 'booking_created' as type, u.first_name, u.last_name, b.created_at as timestamp, CONCAT(c.make, ' ', c.model) as car_info
                     FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    ORDER BY timestamp DESC LIMIT ?
                ";
                $params = [$limit];
            } else {
                $query = "
                    (SELECT 'car_listed' as type, u.first_name, u.last_name, c.created_at as timestamp, CONCAT(c.make, ' ', c.model) as car_info
                     FROM cars c JOIN users u ON c.user_id = u.id WHERE c.user_id = ? AND c.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    UNION ALL
                    (SELECT 'booking_received' as type, u.first_name, u.last_name, b.created_at as timestamp, CONCAT(c.make, ' ', c.model) as car_info
                     FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id WHERE c.user_id = ? AND b.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    ORDER BY timestamp DESC LIMIT ?
                ";
                $params = [$_SESSION['user_id'], $_SESSION['user_id'], $limit];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $activities = $stmt->fetchAll();
            
            success_response(['activities' => $activities]);
        } catch (Exception $e) {
            error_response('Failed to fetch recent activity: ' . $e->getMessage());
        }
    }
}
?>