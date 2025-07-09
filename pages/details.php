<?php
// pages/details.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get car ID from URL
$car_id = (int)($_GET['id'] ?? 0);

if (!$car_id) {
    redirect('browse');
}

// Get car details
$car = new Car();
$car_details = $car->findById($car_id);

if (!$car_details) {
    redirect('browse');
}

// Get car images
$car_images = $car->getImages($car_id);

// Get similar cars (same vehicle type, different car)
$similar_cars = $car->getAll([
    'vehicle_type' => $car_details['vehicle_type']
], 3, 0);

// Remove current car from similar cars
$similar_cars = array_filter($similar_cars, function($similar_car) use ($car_id) {
    return $similar_car['id'] != $car_id;
});

// Get current user
$current_user = get_logged_in_user();
$current_page = 'browse';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle booking form submission
// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    try {
        error_log("Form submitted with data: " . print_r($_POST, true));

        // CSRF protection (optional for testing)
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception("CSRF token validation failed.");
        }

        // Use default user for testing
        $user_id = $current_user ? $current_user['id'] : 1;

        $car_id = (int)$_POST['car_id'];
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        $pickup_location = trim($_POST['pickup_location']);
        $total_amount = (float)$_POST['total_amount'];
        $security_deposit = (float)$_POST['security_deposit'];
        $notes = trim($_POST['notes'] ?? '');

        // Basic validation
        if (empty($start_date) || empty($end_date) || empty($pickup_location)) {
            throw new Exception("Please fill in all required booking details.");
        }

        if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Start date cannot be in the past.");
        }

        if (strtotime($end_date) <= strtotime($start_date)) {
            throw new Exception("End date must be after the start date.");
        }

        // Check for car availability using the fixed method
        if (!$car->isAvailable($car_id, $start_date, $end_date)) {
            throw new Exception("The car is not available for the selected dates.");
        }
        
        $booking_data = [
            'user_id' => $user_id,
            'car_id' => $car_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_amount' => $total_amount,
            'security_deposit' => $security_deposit,
            'pickup_location' => $pickup_location,
            'notes' => $notes
        ];

        $booking_obj = new Booking();
        $booking_id = $booking_obj->create($booking_data);

        if ($booking_id) {
            $_SESSION['success_message'] = "Booking placed successfully! Your booking ID is #" . $booking_id;
            // Redirect to prevent resubmission
            redirect('details?id=' . $car_id);
        } else {
            throw new Exception("Failed to create booking.");
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        error_log("Booking error: " . $e->getMessage());
    }
    redirect('details?id=' . $car_id);
}

// Calculate default pricing (3 days)
$default_days = 3;
$daily_rate = $car_details['price_per_day'];
$service_fee = 20;
$insurance = 15;
$subtotal = $daily_rate * $default_days;
$total = $subtotal + $service_fee + $insurance;

// Set default main image
$main_image = !empty($car_images) ? $car_images[0]['image_path'] : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik0xNDAgMjU1YzAgMCA4MC04MCAxNTAtODB2MzVjLTgwIDAtMTMwIDQ1LTEzMCA0NWgzODBjMCAwLTUwLTQ1LTEzMC00NXYtMzVjNzAgMCAxNTAgODAgMTUwIDgwbDIwLTIwYzAgMC04MC0xMDAtMTcwLTEwMHMtMTAwIDUwLTEwMCA1MGgtNTBjMCAwLTEwLTUwLTEwMC01MFMxMjAgMjM1IDEyMCAyMzVsMjAgMjB6IiBmaWxsPSIjNjQ3NDhiIi8+PGNpcmNsZSBjeD0iMjIwIiBjeT0iMjU1IiByPSI1MCIgZmlsbD0iIzIxMjEyMSIvPjxjaXJjbGUgY3g9IjU2MCIgY3k9IjI1NSIgcj0iNTAiIGZpbGw9IiMyMTIxMjEiLz48L3N2Zz4=';

// Format rating if it exists
$rating = $car_details['rating'] ? number_format($car_details['rating'], 1) : '5.0';
$review_count = $car_details['review_count'] ?: 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car_details['make'] . ' ' . $car_details['model']); ?> | <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/icons/favicon.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/details.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/partials/header.php'; ?>
    
    <main>
        <section class="detail-section">
            <div class="container">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="breadcrumb">
                    <a href="<?php echo BASE_URL; ?>">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="<?php echo BASE_URL; ?>/browse">Browse Cars</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current"><?php echo htmlspecialchars($car_details['make'] . ' ' . $car_details['model']); ?></span>
                </div>
                
                <div class="car-detail-grid">
                    <div class="car-detail-left">
                        <div class="car-gallery">
                            <div class="main-image">
                                <?php if ($car_details['year'] >= date('Y')): ?>
                                    <div class="gallery-badge">New</div>
                                <?php endif; ?>
                                <button id="gallery-like" class="gallery-like">❤</button>
                                <img id="main-image" src="<?php echo strpos($main_image, 'data:') === 0 ? $main_image : BASE_URL . '/uploads/' . $main_image; ?>" alt="<?php echo htmlspecialchars($car_details['make'] . ' ' . $car_details['model']); ?>">
                            </div>
                            
                            <div class="thumbnails">
                                <?php if (!empty($car_images)): ?>
                                    <?php foreach (array_slice($car_images, 0, 4) as $index => $image): ?>
                                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-src="<?php echo BASE_URL . '/uploads/' . $image['image_path']; ?>">
                                            <img src="<?php echo BASE_URL . '/uploads/' . $image['image_path']; ?>" alt="<?php echo htmlspecialchars($car_details['make'] . ' ' . $car_details['model']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Default placeholder thumbnails -->
                                    <div class="thumbnail active" data-src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik0xNDAgMjU1YzAgMCA4MC04MCAxNTAtODB2MzVjLTgwIDAtMTMwIDQ1LTEzMCA0NWgzODBjMCAwLTUwLTQ1LTEzMC00NXYtMzVjNzAgMCAxNTAgODAgMTUwIDgwbDIwLTIwYzAgMC04MC0xMDAtMTcwLTEwMHMtMTAwIDUwLTEwMCA1MGgtNTBjMCAwLTEwLTUwLTEwMC01MFMxMjAgMjM1IDEyMCAyMzVsMjAgMjB6IiBmaWxsPSIjNjQ3NDhiIi8+PGNpcmNsZSBjeD0iMjIwIiBjeT0iMjU1IiByPSI1MCIgZmlsbD0iIzIxMjEyMSIvPjxjaXJjbGUgY3g9IjU2MCIgY3k9IjI1NSIgcj0iNTAiIGZpbGw9IiMyMTIxMjEiLz48L3N2Zz4=">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik0xNDAgMjU1YzAgMCA4MC04MCAxNTAtODB2MzVjLTgwIDAtMTMwIDQ1LTEzMCA0NWgzODBjMCAwLTUwLTQ1LTEzMC00NXYtMzVjNzAgMCAxNTAgODAgMTUwIDgwbDIwLTIwYzAgMC04MC0xMDAtMTcwLTEwMHMtMTAwIDUwLTEwMCA1MGgtNTBjMCAwLTEwLTUwLTEwMC01MFMxMjAgMjM1IDEyMCAyMzVsMjAgMjB6IiBmaWxsPSIjNjQ3NDhiIi8+PGNpcmNsZSBjeD0iMjIwIiBjeT0iMjU1IiByPSI1MCIgZmlsbD0iIzIxMjEyMSIvPjxjaXJjbGUgY3g9IjU2MCIgY3k9IjI1NSIgcj0iNTAiIGZpbGw9IiMyMTIxMjEiLz48L3N2Zz4=" alt="Car Front View">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="car-info">
                            <h1 class="car-name"><?php echo htmlspecialchars($car_details['make'] . ' ' . $car_details['model'] . ' (' . $car_details['year'] . ')'); ?></h1>
                            <p class="car-subtitle"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $car_details['vehicle_type']))); ?> - <?php echo htmlspecialchars(ucfirst($car_details['fuel_type'])); ?></p>
                            
                            <div class="rating-location">
                                <div class="rating">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span><?php echo $rating; ?> (<?php echo $review_count; ?> reviews)</span>
                                </div>
                                
                                <div class="location">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span><?php echo htmlspecialchars($car_details['pickup_location']); ?></span>
                                </div>
                            </div>
                            
                            <div class="feature-section">
                                <h3 class="section-title">Car Features</h3>
                                <div class="features-grid">
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="16 12 12 8 8 12"></polyline>
                                                <line x1="12" y1="16" x2="12" y2="8"></line>
                                            </svg>
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Model Year</span>
                                            <span class="feature-value"><?php echo htmlspecialchars($car_details['year']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Seating Capacity</span>
                                            <span class="feature-value"><?php echo htmlspecialchars($car_details['seating_capacity']); ?> Seats</span>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M23 11h-6a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1z"></path>
                                                <path d="M7 11H1a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1z"></path>
                                                <path d="M15 5h-6a1 1 0 0 0-1 1v15a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1z"></path>
                                                <path d="M4 15h16"></path>
                                            </svg>
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Fuel Type</span>
                                            <span class="feature-value"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $car_details['fuel_type']))); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                            </svg>
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Transmission</span>
                                            <span class="feature-value"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $car_details['transmission']))); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                                <line x1="20" y1="12" x2="20" y2="3"></line>
                                                <line x1="1" y1="14" x2="7" y2="14"></line>
                                                <line x1="9" y1="8" x2="15" y2="8"></line>
                                                <line x1="17" y1="16" x2="23" y2="16"></line>
                                            </svg>
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Vehicle Type</span>
                                            <span class="feature-value"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $car_details['vehicle_type']))); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="description">
                                <h3 class="section-title">Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($car_details['description'])); ?></p>
                            </div>
                            
                            <div class="car-owner">
                                <div class="owner-avatar">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iIzRmNDZlNSIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSI1MCIgZmlsbD0iI2ZmZmZmZiIvPjxwYXRoIGQ9Ik0zNSAxODBjMC01MCAxMzAtNTAgMTMwIDBaIiBmaWxsPSIjZmZmZmZmIi8+PC9zdmc+" alt="Owner Avatar">
                                </div>
                                <div class="owner-details">
                                    <h3 class="owner-name"><?php echo htmlspecialchars($car_details['first_name'] . ' ' . substr($car_details['last_name'], 0, 1) . '.'); ?></h3>
                                    <p class="owner-since">Ride Host since 2023</p>
                                    <div class="owner-rating">
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" color="#FBBF24">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span>5.0 (<?php echo rand(20, 97); ?> reviews)</span>
                                    </div>
                                </div>
                                <button class="contact-owner" onclick="alert('Contact: <?php echo htmlspecialchars($car_details['phone']); ?>')">
                                    Contact Owner
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($similar_cars)): ?>
                        <div class="similar-section">
                            <h2 class="similar-title">Similar Cars You Might Like</h2>
                            <div class="similar-cars">
                                <?php foreach (array_slice($similar_cars, 0, 3) as $similar_car): ?>
                                    <div class="car-card">
                                        <div class="car-image">
                                            <button class="car-like">❤</button>
                                            <img src="<?php echo $similar_car['primary_image'] ? BASE_URL . '/uploads/' . $similar_car['primary_image'] : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik03MCAxNzBjMCAwIDQwLTQwIDEzMC00MHM2MCA0MCAxMzAgNDBsMTAtMTBjMCAwLTMwLTUwLTE0MC01MFM4MCAxNjAgNzAgMTcweiIgZmlsbD0iIzBmNzY2ZSIvPjxjaXJjbGUgY3g9IjExMCIgY3k9IjE3MCIgcj0iMzAiIGZpbGw9IiMyMTIxMjEiLz48Y2lyY2xlIGN4PSIyODAiIGN5PSIxNzAiIHI9IjMwIiBmaWxsPSIjMjEyMTIxIi8+PC9zdmc+'; ?>" alt="<?php echo htmlspecialchars($similar_car['make'] . ' ' . $similar_car['model']); ?>">
                                        </div>
                                        <div class="car-details">
                                            <h3 class="car-title"><?php echo htmlspecialchars($similar_car['make'] . ' ' . $similar_car['model']); ?></h3>
                                            <p class="car-type"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $similar_car['vehicle_type']))); ?></p>
                                            
                                            <div class="car-features">
                                                <div class="car-feature">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                    </svg>
                                                    <?php echo $similar_car['seating_capacity']; ?> Seats
                                                </div>
                                                <div class="car-feature">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M23 11h-6a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1z"></path>
                                                        <path d="M7 11H1a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1z"></path>
                                                        <path d="M15 5h-6a1 1 0 0 0-1 1v15a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1z"></path>
                                                        <path d="M4 15h16"></path>
                                                    </svg>
                                                    <?php echo htmlspecialchars(ucfirst($similar_car['fuel_type'])); ?>
                                                </div>
                                                <div class="car-feature">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                    </svg>
                                                    <?php echo $similar_car['rating'] ? number_format($similar_car['rating'], 1) : '5.0'; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="car-price">
                                                <div class="price-amount">
                                                    <?php echo format_currency($similar_car['price_per_day']); ?><span class="price-period">/day</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="car-detail-right">
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-price">
                                    <?php echo format_currency($daily_rate); ?><span class="price-period">/day</span>
                                </div>
                                <div class="booking-rating">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" color="#FBBF24">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span><?php echo $rating; ?> (<?php echo $review_count; ?> reviews)</span>
                                </div>
                            </div>
                            
                            <form method="POST" class="booking-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="submit_booking" value="1">
                                <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car_id); ?>">
                                <input type="hidden" name="total_amount" id="hidden_total_amount" value="">
                                <input type="hidden" name="security_deposit" id="hidden_security_deposit" value="<?php echo htmlspecialchars($car_details['security_deposit']); ?>">
                                
                                <div class="form-group">
                                    <label class="form-label">When do you need this car?</label>
                                    <div class="date-inputs">
                                        <input type="date" class="date-input" name="start_date" id="start_date" required min="<?php echo date('Y-m-d'); ?>">
                                        <input type="date" class="date-input" name="end_date" id="end_date" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Pickup Location</label>
                                    <select class="form-select" name="pickup_location">
                                        <option value="<?php echo htmlspecialchars($car_details['pickup_location']); ?>"><?php echo htmlspecialchars($car_details['pickup_location']); ?> (Default)</option>
                                        <option value="Bole, Addis Ababa">Bole, Addis Ababa</option>
                                        <option value="Kazanchis, Addis Ababa">Kazanchis, Addis Ababa</option>
                                        <option value="Mexico Square, Addis Ababa">Mexico Square, Addis Ababa</option>
                                        <option value="Megenagna, Addis Ababa">Megenagna, Addis Ababa</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Special Requests (Optional)</label>
                                    <textarea class="form-textarea" name="notes" placeholder="Any special requests or notes..."></textarea>
                                </div>
                                
                                <div class="booking-summary">
                                    <div class="summary-item">
                                        <span id="price-calculation"><?php echo format_currency($daily_rate); ?> x <?php echo $default_days; ?> days</span>
                                        <span id="subtotal-amount"><?php echo format_currency($subtotal); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span>Service fee</span>
                                        <span><?php echo format_currency($service_fee); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span>Insurance</span>
                                        <span><?php echo format_currency($insurance); ?></span>
                                    </div>
                                    <div class="summary-item total">
                                        <span>Total</span>
                                        <span id="total-amount"><?php echo format_currency($total); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (is_logged_in()): ?>
                                    <button type="submit" class="book-now-btn">Book Now</button>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/login" class="book-now-btn" style="text-align: center; text-decoration: none;">Sign In to Book</a>
                                <?php endif; ?>
                                
                                <p class="booking-note">You won't be charged yet</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="<?php echo BASE_URL; ?>" class="footer-logo">
                        <div class="logo-mark">R</div>
                        <?php echo htmlspecialchars(APP_NAME); ?>
                    </a>
                    <p class="footer-description">Peer-to-peer car sharing marketplace connecting car owners with people who need a car. Find, book, and unlock cars directly from your phone.</p>
                </div>
                
                <div>
                    <h4 class="footer-title">Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Investors</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Safety</a></li>
                        <li><a href="#">Insurance</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Host</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/host">List Your Car</a></li>
                        <li><a href="#">Host Resources</a></li>
                        <li><a href="#">Insurance Coverage</a></li>
                        <li><a href="#">Success Stories</a></li>
                        <li><a href="#">Earnings Calculator</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>© 2025 <?php echo htmlspecialchars(APP_NAME); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Pass PHP variables to JavaScript
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        window.CAR_DATA = {
            dailyRate: <?php echo $daily_rate; ?>,
            serviceFee: <?php echo $service_fee; ?>,
            insurance: <?php echo $insurance; ?>
        };
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/details.js"></script>
</body>
</html>