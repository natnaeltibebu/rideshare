<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token. Please refresh the page and try again.');
        }

        $action = $_POST['action'] ?? '';
        
        if ($action === 'login') {
            handleLogin();
        } elseif ($action === 'register') {
            handleRegister();
        } else {
            throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $form_type = $_POST['action'] ?? 'login';
    }
}

function handleLogin() {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Basic validation
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    $user = new User();
    $user_data = $user->findByEmail($email);
    
    if (!$user_data || !$user->verifyPassword($password, $user_data['password'])) {
        throw new Exception('Invalid email or password');
    }
    
    // Create session
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_email'] = $user_data['email'];
    $_SESSION['user_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        // In a real app, you'd store this token in the database
        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
    }
    
    // Set success message and redirect
    $_SESSION['success_message'] = 'Welcome back! You have been successfully logged in.';
    
    // Redirect to intended page or dashboard
    $redirect_url = $_SESSION['redirect_after_login'] ?? '/';
    unset($_SESSION['redirect_after_login']);
    redirect($redirect_url);
}

function handleRegister() {
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'renter');
    $terms = isset($_POST['terms']);
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }
    
    if (!in_array($role, ['renter', 'host'])) {
        $errors[] = 'Please select a valid account type';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the Terms of Service';
    }
    
    $user = new User();
    if ($user->emailExists($email)) {
        $errors[] = 'An account with this email address already exists';
    }
    
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }
    
    // Create user
    $user_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'phone' => '' // Optional phone field
    ];
    
    $user_id = $user->create($user_data);
    
    if ($user_id) {
        // Auto login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        
        $_SESSION['success_message'] = 'Account created successfully! Welcome to Rideshare.';
        redirect('/');
    } else {
        throw new Exception('Failed to create account. Please try again.');
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Preserve form data on error
$form_data = [
    'email' => $_POST['email'] ?? '',
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'role' => $_POST['role'] ?? 'renter',
    'remember' => isset($_POST['remember']),
    'terms' => isset($_POST['terms'])
];

// Determine which tab to show (login or signup)
$active_tab = $form_type ?? ($_GET['tab'] ?? 'login');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up | Rideshare</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/icons/favicon.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <div class="logo-icon">R</div>
                Rideshare
            </a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="auth-tabs">
            <div class="tab-indicator<?php echo $active_tab === 'register' ? ' right' : ''; ?>" id="tabIndicator"></div>
            <div class="auth-tab<?php echo $active_tab === 'login' ? ' active' : ''; ?>" data-tab="login">Sign In</div>
            <div class="auth-tab<?php echo $active_tab === 'register' ? ' active' : ''; ?>" data-tab="signup">Sign Up</div>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <div class="auth-form<?php echo $active_tab === 'register' ? ' hidden' : ''; ?>" id="loginForm">
                <form method="POST" id="loginFormElement">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" class="form-input" placeholder="name@example.com" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-input" id="passwordInput" 
                                   placeholder="••••••••" required>
                            <div class="form-input-icon" id="togglePassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div class="remember-me" style="margin-bottom: 0;">
                            <input type="checkbox" name="remember" id="remember" <?php echo $form_data['remember'] ? 'checked' : ''; ?>>
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="form-forgot">
                            <a href="#" onclick="alert('Password reset functionality will be implemented soon.')">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="loading" style="display: none;"></span>
                    </button>

                    <div class="social-divider">or continue with</div>

                    <div class="social-buttons">
                        <button type="button" class="btn btn-social" onclick="alert('Social login will be implemented soon.')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#4267B2">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                            </svg>
                            Facebook
                        </button>
                        <button type="button" class="btn btn-social" onclick="alert('Social login will be implemented soon.')">
                            <svg width="18" height="18" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                            </svg>
                            Google
                        </button>
                    </div>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="auth-form<?php echo $active_tab === 'login' ? ' hidden' : ''; ?>" id="signupForm">
                <form method="POST" id="signupFormElement">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="first_name" class="form-input" placeholder="John" 
                                   value="<?php echo htmlspecialchars($form_data['first_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="last_name" class="form-input" placeholder="Doe" 
                                   value="<?php echo htmlspecialchars($form_data['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" class="form-input" placeholder="name@example.com" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-input" id="signupPasswordInput" 
                                   placeholder="••••••••" required minlength="8">
                            <div class="form-input-icon" id="toggleSignupPassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">
                            Password must be at least 8 characters with uppercase, lowercase, and number
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Type</label>
                        <div class="role-selector">
                            <div class="role-option">
                                <input type="radio" name="role" value="renter" id="role_renter" 
                                       <?php echo $form_data['role'] === 'renter' ? 'checked' : ''; ?> required>
                                <label for="role_renter" class="role-label">
                                    <div class="role-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <div class="role-info">
                                        <div class="role-title">Renter</div>
                                        <div class="role-desc">I want to rent cars</div>
                                    </div>
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" name="role" value="host" id="role_host" 
                                       <?php echo $form_data['role'] === 'host' ? 'checked' : ''; ?> required>
                                <label for="role_host" class="role-label">
                                    <div class="role-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5c0 .4.2.8.5 1l4 3.5H8a2 2 0 0 0 2 2h1v-2a2 2 0 0 0-2-2"></path>
                                            <circle cx="7" cy="17" r="2"></circle>
                                            <path d="M9 17h6"></path>
                                            <circle cx="17" cy="17" r="2"></circle>
                                        </svg>
                                    </div>
                                    <div class="role-info">
                                        <div class="role-title">Host</div>
                                        <div class="role-desc">I want to list my cars</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" name="terms" id="terms" required <?php echo $form_data['terms'] ? 'checked' : ''; ?>>
                        <label for="terms">I agree to the <a href="#" style="color: var(--primary);">Terms of Service</a></label>
                    </div>

                    <button type="submit" class="btn btn-primary" id="signupBtn">
                        <span class="btn-text">Create Account</span>
                        <span class="loading" style="display: none;"></span>
                    </button>

                    <div class="social-divider">or sign up with</div>

                    <div class="social-buttons">
                        <button type="button" class="btn btn-social" onclick="alert('Social registration will be implemented soon.')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#4267B2">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                            </svg>
                            Facebook
                        </button>
                        <button type="button" class="btn btn-social" onclick="alert('Social registration will be implemented soon.')">
                            <svg width="18" height="18" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                            </svg>
                            Google
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="auth-footer">
            By continuing, you agree to Rideshare's
            <a href="#">Terms of Service</a> and
            <a href="#">Privacy Policy</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.auth-tab');
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const tabIndicator = document.getElementById('tabIndicator');
            const togglePassword = document.getElementById('togglePassword');
            const toggleSignupPassword = document.getElementById('toggleSignupPassword');
            const passwordInput = document.getElementById('passwordInput');
            const signupPasswordInput = document.getElementById('signupPasswordInput');
            const loginFormElement = document.getElementById('loginFormElement');
            const signupFormElement = document.getElementById('signupFormElement');
            const loginBtn = document.getElementById('loginBtn');
            const signupBtn = document.getElementById('signupBtn');

            // Tab switching
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    tab.classList.add('active');

                    // Move indicator and switch forms
                    if (tab.dataset.tab === 'signup') {
                        tabIndicator.classList.add('right');
                        loginForm.classList.add('hidden');
                        setTimeout(() => {
                            signupForm.classList.remove('hidden');
                        }, 150);
                    } else {
                        tabIndicator.classList.remove('right');
                        signupForm.classList.add('hidden');
                        setTimeout(() => {
                            loginForm.classList.remove('hidden');
                        }, 150);
                    }
                });
            });

            // Password toggle functionality
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? 
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' : 
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                });
            }

            if (toggleSignupPassword && signupPasswordInput) {
                toggleSignupPassword.addEventListener('click', function() {
                    const type = signupPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    signupPasswordInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? 
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' : 
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                });
            }

            // Form submission handlers
            function showLoading(btn) {
                btn.disabled = true;
                btn.querySelector('.btn-text').style.display = 'none';
                btn.querySelector('.loading').style.display = 'inline-block';
            }

            function hideLoading(btn) {
                btn.disabled = false;
                btn.querySelector('.btn-text').style.display = 'inline';
                btn.querySelector('.loading').style.display = 'none';
            }

            if (loginFormElement) {
                loginFormElement.addEventListener('submit', function(e) {
                    showLoading(loginBtn);
                });
            }

            if (signupFormElement) {
                signupFormElement.addEventListener('submit', function(e) {
                    const password = signupPasswordInput.value;
                    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/;
                    
                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long');
                        return;
                    }
                    
                    if (!passwordRegex.test(password)) {
                        e.preventDefault();
                        alert('Password must contain at least one uppercase letter, one lowercase letter, and one number');
                        return;
                    }
                    
                    showLoading(signupBtn);
                });
            }

            // Add ripple effect to primary buttons
            const buttons = document.querySelectorAll('.btn-primary');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    this.appendChild(ripple);
                    
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        pointer-events: none;
                        width: 100px;
                        height: 100px;
                        top: ${y - 50}px;
                        left: ${x - 50}px;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                    `;
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>