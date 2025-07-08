<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: " . BASE_URL . "/" . ltrim($url, '/'));
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login');
    }
}

function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $user = new User();
    // Try getUserById first, fallback to findById if needed
    if (method_exists($user, 'getUserById')) {
        return $user->getUserById($_SESSION['user_id']);
    } else {
        return $user->findById($_SESSION['user_id']);
    }
}

function upload_file($file, $destination_folder, $allowed_types = ALLOWED_IMAGE_TYPES) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size too large');
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Invalid file type');
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $destination = UPLOAD_PATH . $destination_folder . '/' . $filename;
    
    if (!is_dir(UPLOAD_PATH . $destination_folder)) {
        mkdir(UPLOAD_PATH . $destination_folder, 0755, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to upload file');
    }
    
    return $destination_folder . '/' . $filename;
}

function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function calculate_days_between($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    return $start->diff($end)->days + 1;
}

function get_car_features($car) {
    $features = [];
    $features[] = $car['seating_capacity'] . ' Seats';
    $features[] = ucfirst($car['fuel_type']);
    if (isset($car['rating'])) {
        $features[] = number_format($car['rating'], 1);
    }
    return $features;
}

function has_role($role) {
    $user = get_logged_in_user();
    return $user && $user['role'] === $role;
}

function is_admin() {
    return has_role('admin');
}

function is_host() {
    return has_role('host');
}

function is_renter() {
    return has_role('renter');
}

function success_response($data = [], $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function error_response($message = 'Error', $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}
?>