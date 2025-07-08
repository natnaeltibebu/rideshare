<?php
// Application configuration
define('APP_NAME', 'Rideshare');
define('BASE_URL', 'http://localhost/rideshare');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Security
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Pagination
define('CARS_PER_PAGE', 12);
define('BOOKINGS_PER_PAGE', 10);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database class
require_once __DIR__ . '/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../includes/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>