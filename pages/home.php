<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get current user
$current_user = get_logged_in_user();
$current_page = 'home';

// Get featured cars
$car = new Car();
$featured_cars = $car->getAll([], 4, 0, 'RAND()'); // Get 4 featured cars

// Handle search form submission
$search_performed = false;
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_cars'])) {
    $search_filters = [
        'location' => sanitize_input($_POST['location'] ?? ''),
    ];
    
    $search_results = $car->getAll($search_filters, 12, 0);
    $search_performed = true;
}

// Generate CSRF token for forms
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(APP_NAME); ?> | Modern Car Sharing Marketplace</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/icons/favicon.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/home.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/partials/header.php'; ?>
    
    <main>
        <?php if ($search_performed): ?>
            <section class="cars-section" style="padding-top: 3rem;">
                <div class="container">
                    <div class="section-header">
                        <div>
                            <div class="section-badge">Search Results</div>
                            <h2 class="section-title">Found <?php echo count($search_results); ?> cars</h2>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/browse" class="view-all">View all <span>→</span></a>
                    </div>
                    
                    <div class="cars-grid">
                        <?php foreach ($search_results as $car): ?>
                            <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                                <div class="car-image">
                                    <?php if ($car['year'] >= date('Y')): ?>
                                        <div class="car-badge">New</div>
                                    <?php endif; ?>
                                    <button class="car-like">❤</button>
                                    <img src="<?php echo $car['primary_image'] ? BASE_URL . '/uploads/' . $car['primary_image'] : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik03MCAxNzBjMCAwIDQwLTQwIDEzMC00MHM2MCA0MCAxMzAgNDBsMTAtMTBjMCAwLTMwLTUwLTE0MC01MFM4MCAxNjAgNzAgMTcweiIgZmlsbD0iIzY0NzQ4YiIvPjxjaXJjbGUgY3g9IjExMCIgY3k9IjE3MCIgcj0iMzAiIGZpbGw9IiMyMTIxMjEiLz48Y2lyY2xlIGN4PSIyODAiIGN5PSIxNzAiIHI9IjMwIiBmaWxsPSIjMjEyMTIxIi8+PC9zdmc+'; ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                </div>
                                <div class="car-details">
                                    <h3 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                                    <p class="car-type"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $car['vehicle_type']))); ?></p>
                                    
                                    <div class="car-features">
                                        <div class="car-feature">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                            </svg>
                                            <span class="car-feature-text"><?php echo htmlspecialchars($car['seating_capacity']); ?> Seats</span>
                                        </div>
                                        <div class="car-feature">
                                            <?php if ($car['transmission'] === 'manual'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M1.5 6.75v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m2.85-.45-2.25-3a.75.75 0 1 0-1.2.9l2.25 3a.75.75 0 1 0 1.2-.9m18.9 13.95h-3L21 21v-.75A2.25 2.25 0 0 1 23.25 18l-.75-.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75 3.75 3.75 0 0 0-3.75 3.75V21c0 .414.336.75.75.75h3a.75.75 0 0 0 0-1.5M18.024 2.056A.75.75 0 1 1 18.75 3v1.5a.75.75 0 1 1-.722.95.75.75 0 1 0-1.446.4A2.25 2.25 0 1 0 18.75 3c-1 0-1 1.5 0 1.5a2.25 2.25 0 1 0-2.174-2.832.75.75 0 0 0 1.448.388M12 18.75a.75.75 0 0 1 1.5 0c0 .315-.107.622-.304.868l-2.532 3.163A.75.75 0 0 0 11.25 24h3a.75.75 0 0 0 0-1.5h-3l.586 1.219 2.532-3.164c.41-.513.632-1.15.632-1.805a2.25 2.25 0 0 0-4.5 0 .75.75 0 0 0 1.5 0M8.25 1.5H9v5.25a.75.75 0 0 0 1.5 0V1.5A1.5 1.5 0 0 0 9 0h-.75a.75.75 0 0 0 0 1.5m0 6h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 0 0 1.5m-6-7.5H.75A.75.75 0 0 0 0 .75v3c0 .414.336.75.75.75h1.5a2.25 2.25 0 0 0 0-4.5m0 1.5a.75.75 0 0 1 0 1.5H.75l.75.75v-3l-.75.75zm8.25 11.25v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m1.5 0v1.5a.75.75 0 0 0 1.5 0v-1.5a.75.75 0 0 0-1.5 0m7.5 0v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m3 1.5A2.25 2.25 0 0 0 20.25 12h-15A2.25 2.25 0 0 1 3 9.75a.75.75 0 0 0-1.5 0 3.75 3.75 0 0 0 3.75 3.75h15a.75.75 0 0 1 .75.75.75.75 0 0 0 1.5 0"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M19.25 14.25v-4.5l-1.374.416 3 4.5c.412.617 1.374.326 1.374-.416v-4.5a.75.75 0 0 0-1.5 0v4.5l1.374-.416-3-4.5c-.412-.617-1.374-.326-1.374.416v4.5a.75.75 0 0 0 1.5 0m3 6a3.75 3.75 0 0 0-3.75-3.75.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75a3.75 3.75 0 0 0 3.75-3.75m-1.5 0a2.25 2.25 0 0 1-2.25 2.25l.75.75v-6l-.75.75a2.25 2.25 0 0 1 2.25 2.25M18.5 4.5H20A2.25 2.25 0 0 0 20 0h-1.5a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6l-.75.75H20A.75.75 0 0 1 20 3h-1.5a.75.75 0 0 0 0 1.5M4.25 6.75v4.5A2.25 2.25 0 0 0 6.5 13.5H8a.75.75 0 0 1 .75.75v4.5A2.25 2.25 0 0 0 11 21h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 1-.75-.75v-4.5A2.25 2.25 0 0 0 8 12H6.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-1.5 0m3-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0m1.5 0a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0"></path>
                                                </svg>
                                            <?php endif; ?>
                                            <span class="car-feature-text"><?php echo htmlspecialchars(ucfirst($car['transmission'])); ?></span>
                                        </div>
                                        <div class="car-feature">
                                            <?php if ($car['fuel_type'] === 'electric'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M14.69 2.21L4.33 11.49c-.64.58-.28 1.65.58 1.73L8 13.64V19c0 .55.45 1 1 1s1-.45 1-1v-6.14L12.96 11l1.06-2.23L16.31 7l5.74-.5c.86-.08 1.22-1.15.58-1.73L12.27.21c-.39-.35-.98-.35-1.37 0L9.69 2.21c-.64.58-.28 1.65.58 1.73z"/>
                                                </svg>
                                            <?php elseif ($car['fuel_type'] === 'hybrid'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M19 12h-2l3.35-6.71c.12-.25.15-.56.07-.83-.08-.27-.26-.5-.51-.65C19.66 3.66 19.34 3.66 19.09 3.81L16 6V3c0-.55-.45-1-1-1s-1 .45-1 1v3.17L10.55 4.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89v4.38L6.45 8.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89V12H3c-.55 0-1 .45-1 1s.45 1 1 1h2v2.21c0 .37.19.7.48.89.29.19.66.19.95 0L9.12 15.83V18c0 .37.19.7.48.89.14.09.3.14.47.14s.33-.05.47-.14c.29-.19.48-.52.48-.89v-2.17L14 17.1c.29.19.66.19.95 0 .29-.19.48-.52.48-.89V14h2c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                                                </svg>
                                            <?php else: ?>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M3,2A1,1 0 0,0 2,3V19A3,3 0 0,0 5,22H11A3,3 0 0,0 14,19V16H16V19A1,1 0 0,0 17,20H18A1,1 0 0,0 19,19V12A1,1 0 0,0 18,11H17A1,1 0 0,0 16,12V14H14V3A1,1 0 0,0 13,2H3M4,4H12V12H4V4M6,6V10H10V6H6Z"/>
                                                </svg>
                                            <?php endif; ?>
                                            <span class="car-feature-text"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $car['fuel_type']))); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="car-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        <?php echo htmlspecialchars(explode(',', $car['pickup_location'])[0]); ?>
                                    </div>
                                    
                                    <div class="car-price">
                                        <div class="price-amount">
                                            <?php echo format_currency($car['price_per_day']); ?><span class="price-period">/day</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="hero">
                <div class="container">
                    <div class="hero-content">
                        <div class="hero-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            The Future of Car Sharing
                        </div>
                        <h1 class="display">Drive your perfect match, anytime</h1>
                        <p class="subtitle">Discover and book the ideal car for every occasion. Thousands of vehicles available in your city with insurance included.</p>
                    </div>
                    
                    <div class="search-container">
                        <form class="search-form" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="search_cars" value="1">
                            
                            <div class="search-group">
                                <label class="search-label">Location</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="search-input" id="home-location" name="location" placeholder="Search in Addis Ababa..." autocomplete="off" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                                    <div class="autocomplete-results" id="home-location-results"></div>
                                </div>
                            </div>
                            
                            <div class="search-group">
                                <label class="search-label">Pickup Date</label>
                                <input type="date" class="search-input" name="pickup_date" value="<?php echo htmlspecialchars($_POST['pickup_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="search-group">
                                <label class="search-label">Return Date</label>
                                <input type="date" class="search-input" name="return_date" value="<?php echo htmlspecialchars($_POST['return_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary search-button">Search Cars</button>
                        </form>
                    </div>
                </div>
            </section>
            
            <section class="cars-section">
                <div class="container">
                    <div class="section-header">
                        <div>
                            <div class="section-badge">Featured Cars</div>
                            <h2 class="section-title">Popular in your area</h2>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/browse" class="view-all">View all <span>→</span></a>
                    </div>
                    
                    <div class="cars-filter">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="electric">Electric</button>
                        <button class="filter-btn" data-filter="suv">SUVs</button>
                        <button class="filter-btn" data-filter="sedan">Sedans</button>
                        <button class="filter-btn" data-filter="luxury">Luxury</button>
                        <button class="filter-btn" data-filter="economy">Economy</button>
                        <button class="filter-btn" data-filter="convertible">Convertible</button>
                    </div>
                    
                    <div class="cars-grid">
                        <?php if (!empty($featured_cars)): ?>
                            <?php foreach ($featured_cars as $car): ?>
                                <div class="car-card" data-type="<?php echo $car['vehicle_type']; ?>" data-fuel="<?php echo $car['fuel_type']; ?>" data-car-id="<?php echo $car['id']; ?>">
                                    <div class="car-image">
                                        <?php if ($car['year'] >= date('Y')): ?>
                                            <div class="car-badge">New</div>
                                        <?php endif; ?>
                                        <button class="car-like">❤</button>
                                        <img src="<?php echo $car['primary_image'] ? BASE_URL . '/uploads/' . $car['primary_image'] : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik03MCAxNzBjMCAwIDQwLTQwIDEzMC00MHM2MCA0MCAxMzAgNDBsMTAtMTBjMCAwLTMwLTUwLTE0MC01MFM4MCAxNjAgNzAgMTcweiIgZmlsbD0iIzY0NzQ4YiIvPjxjaXJjbGUgY3g9IjExMCIgY3k9IjE3MCIgcj0iMzAiIGZpbGw9IiMyMTIxMjEiLz48Y2lyY2xlIGN4PSIyODAiIGN5PSIxNzAiIHI9IjMwIiBmaWxsPSIjMjEyMTIxIi8+PC9zdmc+'; ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                    </div>
                                    <div class="car-details">
                                        <h3 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                                        <p class="car-type"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $car['vehicle_type']))); ?></p>
                                        
                                        <div class="car-features">
                                            <div class="car-feature">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                                </svg>
                                                <span class="car-feature-text"><?php echo htmlspecialchars($car['seating_capacity']); ?> Seats</span>
                                            </div>
                                            <div class="car-feature">
                                                <?php if ($car['transmission'] === 'manual'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <path d="M1.5 6.75v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m2.85-.45-2.25-3a.75.75 0 1 0-1.2.9l2.25 3a.75.75 0 1 0 1.2-.9m18.9 13.95h-3L21 21v-.75A2.25 2.25 0 0 1 23.25 18l-.75-.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75 3.75 3.75 0 0 0-3.75 3.75V21c0 .414.336.75.75.75h3a.75.75 0 0 0 0-1.5M18.024 2.056A.75.75 0 1 1 18.75 3v1.5a.75.75 0 1 1-.722.95.75.75 0 1 0-1.446.4A2.25 2.25 0 1 0 18.75 3c-1 0-1 1.5 0 1.5a2.25 2.25 0 1 0-2.174-2.832.75.75 0 0 0 1.448.388M12 18.75a.75.75 0 0 1 1.5 0c0 .315-.107.622-.304.868l-2.532 3.163A.75.75 0 0 0 11.25 24h3a.75.75 0 0 0 0-1.5h-3l.586 1.219 2.532-3.164c.41-.513.632-1.15.632-1.805a2.25 2.25 0 0 0-4.5 0 .75.75 0 0 0 1.5 0M8.25 1.5H9v5.25a.75.75 0 0 0 1.5 0V1.5A1.5 1.5 0 0 0 9 0h-.75a.75.75 0 0 0 0 1.5m0 6h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 0 0 1.5m-6-7.5H.75A.75.75 0 0 0 0 .75v3c0 .414.336.75.75.75h1.5a2.25 2.25 0 0 0 0-4.5m0 1.5a.75.75 0 0 1 0 1.5H.75l.75.75v-3l-.75.75zm8.25 11.25v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m1.5 0v1.5a.75.75 0 0 0 1.5 0v-1.5a.75.75 0 0 0-1.5 0m7.5 0v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m3 1.5A2.25 2.25 0 0 0 20.25 12h-15A2.25 2.25 0 0 1 3 9.75a.75.75 0 0 0-1.5 0 3.75 3.75 0 0 0 3.75 3.75h15a.75.75 0 0 1 .75.75.75.75 0 0 0 1.5 0"></path>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <path d="M19.25 14.25v-4.5l-1.374.416 3 4.5c.412.617 1.374.326 1.374-.416v-4.5a.75.75 0 0 0-1.5 0v4.5l1.374-.416-3-4.5c-.412-.617-1.374-.326-1.374.416v4.5a.75.75 0 0 0 1.5 0m3 6a3.75 3.75 0 0 0-3.75-3.75.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75a3.75 3.75 0 0 0 3.75-3.75m-1.5 0a2.25 2.25 0 0 1-2.25 2.25l.75.75v-6l-.75.75a2.25 2.25 0 0 1 2.25 2.25M18.5 4.5H20A2.25 2.25 0 0 0 20 0h-1.5a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6l-.75.75H20A.75.75 0 0 1 20 3h-1.5a.75.75 0 0 0 0 1.5M4.25 6.75v4.5A2.25 2.25 0 0 0 6.5 13.5H8a.75.75 0 0 1 .75.75v4.5A2.25 2.25 0 0 0 11 21h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 1-.75-.75v-4.5A2.25 2.25 0 0 0 8 12H6.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-1.5 0m3-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0m1.5 0a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0"></path>
                                                    </svg>
                                                <?php endif; ?>
                                                <span class="car-feature-text"><?php echo htmlspecialchars(ucfirst($car['transmission'])); ?></span>
                                            </div>
                                            <div class="car-feature">
                                                <?php if ($car['fuel_type'] === 'electric'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <path d="M14.69 2.21L4.33 11.49c-.64.58-.28 1.65.58 1.73L8 13.64V19c0 .55.45 1 1 1s1-.45 1-1v-6.14L12.96 11l1.06-2.23L16.31 7l5.74-.5c.86-.08 1.22-1.15.58-1.73L12.27.21c-.39-.35-.98-.35-1.37 0L9.69 2.21c-.64.58-.28 1.65.58 1.73z"/>
                                                    </svg>
                                                <?php elseif ($car['fuel_type'] === 'hybrid'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <path d="M19 12h-2l3.35-6.71c.12-.25.15-.56.07-.83-.08-.27-.26-.5-.51-.65C19.66 3.66 19.34 3.66 19.09 3.81L16 6V3c0-.55-.45-1-1-1s-1 .45-1 1v3.17L10.55 4.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89v4.38L6.45 8.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89V12H3c-.55 0-1 .45-1 1s.45 1 1 1h2v2.21c0 .37.19.7.48.89.29.19.66.19.95 0L9.12 15.83V18c0 .37.19.7.48.89.14.09.3.14.47.14s.33-.05.47-.14c.29-.19.48-.52.48-.89v-2.17L14 17.1c.29.19.66.19.95 0 .29-.19.48-.52.48-.89V14h2c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M3,2A1,1 0 0,0 2,3V19A3,3 0 0,0 5,22H11A3,3 0 0,0 14,19V16H16V19A1,1 0 0,0 17,20H18A1,1 0 0,0 19,19V12A1,1 0 0,0 18,11H17A1,1 0 0,0 16,12V14H14V3A1,1 0 0,0 13,2H3M4,4H12V12H4V4M6,6V10H10V6H6Z"/>
                                                    </svg>
                                                <?php endif; ?>
                                                <span class="car-feature-text"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $car['fuel_type']))); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="car-location">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                            </svg>
                                            <?php echo htmlspecialchars(explode(',', $car['pickup_location'])[0]); ?>
                                        </div>
                                        
                                        <div class="car-price">
                                            <div class="price-amount">
                                                <?php echo format_currency($car['price_per_day']); ?><span class="price-period">/day</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="car-card" data-car-id="demo">
                                <div class="car-image">
                                    <div class="car-badge">New</div>
                                    <button class="car-like">❤</button>
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YxZjVmOSIvPjxwYXRoIGQ9Ik03MCAxNzBjMCAwIDQwLTQwIDEzMC00MHM2MCA0MCAxMzAgNDBsMTAtMTBjMCAwLTMwLTUwLTE0MC01MFM4MCAxNjAgNzAgMTcweiIgZmlsbD0iIzY0NzQ4YiIvPjxjaXJjbGUgY3g9IjExMCIgY3k9IjE3MCIgcj0iMzAiIGZpbGw9IiMyMTIxMjEiLz48Y2lyY2xlIGN4PSIyODAiIGN5PSIxNzAiIHI9IjMwIiBmaWxsPSIjMjEyMTIxIi8+PC9zdmc+" alt="Tesla Model 3">
                                </div>
                                <div class="car-details">
                                    <h3 class="car-title">Tesla Model 3</h3>
                                    <p class="car-type">Electric Sedan</p>
                                    
                                    <div class="car-features">
                                        <div class="car-feature">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                            </svg>
                                            <span class="car-feature-text">5 Seats</span>
                                        </div>
                                        <div class="car-feature">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M19.25 14.25v-4.5l-1.374.416 3 4.5c.412.617 1.374.326 1.374-.416v-4.5a.75.75 0 0 0-1.5 0v4.5l1.374-.416-3-4.5c-.412-.617-1.374-.326-1.374.416v4.5a.75.75 0 0 0 1.5 0m3 6a3.75 3.75 0 0 0-3.75-3.75.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75a3.75 3.75 0 0 0 3.75-3.75m-1.5 0a2.25 2.25 0 0 1-2.25 2.25l.75.75v-6l-.75.75a2.25 2.25 0 0 1 2.25 2.25M18.5 4.5H20A2.25 2.25 0 0 0 20 0h-1.5a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6l-.75.75H20A.75.75 0 0 1 20 3h-1.5a.75.75 0 0 0 0 1.5M4.25 6.75v4.5A2.25 2.25 0 0 0 6.5 13.5H8a.75.75 0 0 1 .75.75v4.5A2.25 2.25 0 0 0 11 21h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 1-.75-.75v-4.5A2.25 2.25 0 0 0 8 12H6.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-1.5 0m3-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0m1.5 0a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0"></path>
                                            </svg>
                                            <span class="car-feature-text">Automatic</span>
                                        </div>
                                        <div class="car-feature">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M14.69 2.21L4.33 11.49c-.64.58-.28 1.65.58 1.73L8 13.64V19c0 .55.45 1 1 1s1-.45 1-1v-6.14L12.96 11l1.06-2.23L16.31 7l5.74-.5c.86-.08 1.22-1.15.58-1.73L12.27.21c-.39-.35-.98-.35-1.37 0L9.69 2.21c-.64.58-.28 1.65.58 1.73z"/>
                                            </svg>
                                            <span class="car-feature-text">Electric</span>
                                        </div>
                                    </div>
                                    
                                    <div class="car-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                        Bole
                                    </div>
                                    
                                    <div class="car-price">
                                        <div class="price-amount">
                                            $68<span class="price-period">/day</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        
        <section class="faq-section">
            <div class="container">
                <div class="faq-header">
                    <div class="section-badge">FAQ</div>
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="subtitle">Everything you need to know about the <?php echo htmlspecialchars(APP_NAME); ?> platform.</p>
                </div>
                
                <div class="faq-list">
                    <div class="faq-item active">
                        <div class="faq-question">
                            How does <?php echo htmlspecialchars(APP_NAME); ?> work?
                            <div class="faq-icon">+</div>
                        </div>
                        <div class="faq-answer">
                            <?php echo htmlspecialchars(APP_NAME); ?> connects car owners with people who need a car. Browse available cars in your area, book the one you like, and unlock it with your smartphone. All bookings include insurance and roadside assistance. When you're done, return the car to its original location.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            What do I need to rent a car?
                            <div class="faq-icon">+</div>
                        </div>
                        <div class="faq-answer">
                            To rent a car through <?php echo htmlspecialchars(APP_NAME); ?>, you need to be at least 21 years old, have a valid driver's license, and a clean driving record. You'll need to complete a quick verification process when you sign up. The process usually takes less than 24 hours.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            Is insurance included with my booking?
                            <div class="faq-icon">+</div>
                        </div>
                        <div class="faq-answer">
                            Yes, every <?php echo htmlspecialchars(APP_NAME); ?> booking includes comprehensive insurance coverage. The insurance covers damage to the car, liability protection, and 24/7 roadside assistance. There's no need to purchase additional insurance unless you want to reduce your deductible.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I list my car on <?php echo htmlspecialchars(APP_NAME); ?>?
                            <div class="faq-icon">+</div>
                        </div>
                        <div class="faq-answer">
                            Listing your car is simple! Create an account, provide details about your car, set your availability and pricing, and complete the verification process. Once approved, your car will be visible to potential renters in your area.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            What happens if the car gets damaged?
                            <div class="faq-icon">+</div>
                        </div>
                        <div class="faq-answer">
                            If the car gets damaged during your rental, report it immediately through the <?php echo htmlspecialchars(APP_NAME); ?> app. Take photos of the damage and submit them. Your insurance coverage will kick in, and you'll only be responsible for the deductible amount.
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="cta-section">
            <div class="container">
                <div class="cta-card">
                    <div class="cta-shape cta-shape-1"></div>
                    <div class="cta-shape cta-shape-2"></div>
                    
                    <div class="cta-content">
                        <h2 class="cta-title">Ready to drive on your terms?</h2>
                        <p class="cta-subtitle">Join thousands of happy users who have embraced a new way of mobility.</p>
                        
                        <div class="cta-buttons">
                            <a href="<?php echo BASE_URL; ?>/browse" class="btn btn-white btn-lg">Find Cars</a>
                            <a href="<?php echo BASE_URL; ?>/host" class="btn btn-outline btn-lg">List Your Car</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include __DIR__ . '/../includes/partials/footer.php'; ?>

    <script>
        // Pass PHP variables to JavaScript
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/home.js"></script>
</body>
</html>