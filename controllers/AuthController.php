<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            error_response('Email and password are required');
        }
        
        $user = $this->user->findByEmail($email);
        
        if (!$user || !$this->user->verifyPassword($password, $user['password'])) {
            error_response('Invalid credentials');
        }
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        success_response([
            'user' => [
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'email' => $user['email']
            ]
        ], 'Login successful');
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        $data = [
            'first_name' => sanitize_input($_POST['first_name'] ?? ''),
            'last_name' => sanitize_input($_POST['last_name'] ?? ''),
            'email' => sanitize_input($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'phone' => sanitize_input($_POST['phone'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($this->user->emailExists($data['email'])) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            error_response(implode(', ', $errors));
        }
        
        $user_id = $this->user->create($data);
        
        if ($user_id) {
            // Auto login after registration
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            
            success_response([
                'user' => [
                    'id' => $user_id,
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email']
                ]
            ], 'Registration successful');
        } else {
            error_response('Registration failed');
        }
    }
    
    public function logout() {
        session_destroy();
        success_response([], 'Logout successful');
    }
    
    public function getProfile() {
        require_login();
        
        $user = $this->user->findById($_SESSION['user_id']);
        
        if ($user) {
            unset($user['password']);
            success_response(['user' => $user]);
        } else {
            error_response('User not found', 404);
        }
    }
}
?>