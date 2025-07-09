<?php
// pages/browse.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get current user info
$current_user = get_logged_in_user();
$current_page = 'browse';

// Handle search and filters
$search_query = sanitize_input($_GET['search'] ?? '');
$filters = [
    'vehicle_type' => $_GET['vehicle_type'] ?? '',
    'make' => $_GET['make'] ?? '',
    'transmission' => $_GET['transmission'] ?? '',
    'fuel_type' => $_GET['fuel_type'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'seating' => $_GET['seating'] ?? '',
    'available_from' => $_GET['available_from'] ?? '',
    'available_to' => $_GET['available_to'] ?? ''
];

// Handle search in make/model and location
if (!empty($search_query)) {
    // Check if search query contains location keywords for Addis Ababa
    $addis_locations = ['bole', 'kazanchis', 'piassa', 'merkato', 'mexico', 'megenagna', 'sarbet', 'gerji', 'ayat', 'jemo', 'cmc', 'summit', 'lebu', 'kaliti'];
    $is_location_search = false;
    
    foreach ($addis_locations as $location) {
        if (stripos($search_query, $location) !== false) {
            $filters['location'] = $search_query;
            $is_location_search = true;
            break;
        }
    }
    
    // If not a location search, treat as make/model search
    if (!$is_location_search) {
        $filters['make'] = $search_query;
    }
}

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = CARS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Sorting
$sort_by = $_GET['sort'] ?? 'newest';
$order_by = 'c.created_at DESC'; // default

switch ($sort_by) {
    case 'price-low':
        $order_by = 'c.price_per_day ASC';
        break;
    case 'price-high':
        $order_by = 'c.price_per_day DESC';
        break;
    case 'rating':
        $order_by = 'c.rating DESC';
        break;
    case 'newest':
    default:
        $order_by = 'c.created_at DESC';
        break;
}

// Get cars from database
try {
    $car_model = new Car();
    $cars = $car_model->getAll($filters, $limit, $offset, $order_by);
    
    // Get total count for pagination
    $total_cars = $car_model->getTotalCount($filters);
    $total_pages = ceil($total_cars / $limit);
    
} catch (Exception $e) {
    $cars = [];
    $total_cars = 0;
    $total_pages = 0;
    error_log("Error fetching cars: " . $e->getMessage());
}

// Function to generate car image URL
function getCarImageUrl($primary_image) {
    if ($primary_image && file_exists(UPLOAD_PATH . $primary_image)) {
        return BASE_URL . '/uploads/' . $primary_image;
    }
    // Return placeholder SVG if no image
    return "data:image/svg+xml;base64," . base64_encode('
        <svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
            <rect width="400" height="300" fill="#f1f5f9"/>
            <path d="M70 170c0 0 40-40 130-40s60 40 130 40l10-10c0 0-30-50-140-50S80 160 70 170z" fill="#64748b"/>
            <circle cx="110" cy="170" r="30" fill="#212121"/>
            <circle cx="280" cy="170" r="30" fill="#212121"/>
        </svg>
    ');
}

// Function to get car features
function getCarFeatures($car) {
    $features = [];
    $features[] = [
        'icon' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                   </svg>',
        'text' => $car['seating_capacity'] . ' Seats'
    ];
    
    // Transmission icon
    if ($car['transmission'] === 'manual') {
        $transmission_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M1.5 6.75v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m2.85-.45-2.25-3a.75.75 0 1 0-1.2.9l2.25 3a.75.75 0 1 0 1.2-.9m18.9 13.95h-3L21 21v-.75A2.25 2.25 0 0 1 23.25 18l-.75-.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75 3.75 3.75 0 0 0-3.75 3.75V21c0 .414.336.75.75.75h3a.75.75 0 0 0 0-1.5M18.024 2.056A.75.75 0 1 1 18.75 3v1.5a.75.75 0 1 1-.722.95.75.75 0 1 0-1.446.4A2.25 2.25 0 1 0 18.75 3c-1 0-1 1.5 0 1.5a2.25 2.25 0 1 0-2.174-2.832.75.75 0 0 0 1.448.388M12 18.75a.75.75 0 0 1 1.5 0c0 .315-.107.622-.304.868l-2.532 3.163A.75.75 0 0 0 11.25 24h3a.75.75 0 0 0 0-1.5h-3l.586 1.219 2.532-3.164c.41-.513.632-1.15.632-1.805a2.25 2.25 0 0 0-4.5 0 .75.75 0 0 0 1.5 0M8.25 1.5H9v5.25a.75.75 0 0 0 1.5 0V1.5A1.5 1.5 0 0 0 9 0h-.75a.75.75 0 0 0 0 1.5m0 6h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 0 0 1.5m-6-7.5H.75A.75.75 0 0 0 0 .75v3c0 .414.336.75.75.75h1.5a2.25 2.25 0 0 0 0-4.5m0 1.5a.75.75 0 0 1 0 1.5H.75l.75.75v-3l-.75.75zm8.25 11.25v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m1.5 0v1.5a.75.75 0 0 0 1.5 0v-1.5a.75.75 0 0 0-1.5 0m7.5 0v-3a.75.75 0 0 0-1.5 0v3a.75.75 0 0 0 1.5 0m3 1.5A2.25 2.25 0 0 0 20.25 12h-15A2.25 2.25 0 0 1 3 9.75a.75.75 0 0 0-1.5 0 3.75 3.75 0 0 0 3.75 3.75h15a.75.75 0 0 1 .75.75.75.75 0 0 0 1.5 0"></path>
                              </svg>';
    } else {
        $transmission_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M19.25 14.25v-4.5l-1.374.416 3 4.5c.412.617 1.374.326 1.374-.416v-4.5a.75.75 0 0 0-1.5 0v4.5l1.374-.416-3-4.5c-.412-.617-1.374-.326-1.374.416v4.5a.75.75 0 0 0 1.5 0m3 6a3.75 3.75 0 0 0-3.75-3.75.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75a3.75 3.75 0 0 0 3.75-3.75m-1.5 0a2.25 2.25 0 0 1-2.25 2.25l.75.75v-6l-.75.75a2.25 2.25 0 0 1 2.25 2.25M18.5 4.5H20A2.25 2.25 0 0 0 20 0h-1.5a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6l-.75.75H20A.75.75 0 0 1 20 3h-1.5a.75.75 0 0 0 0 1.5M4.25 6.75v4.5A2.25 2.25 0 0 0 6.5 13.5H8a.75.75 0 0 1 .75.75v4.5A2.25 2.25 0 0 0 11 21h3a.75.75 0 0 0 0-1.5h-3a.75.75 0 0 1-.75-.75v-4.5A2.25 2.25 0 0 0 8 12H6.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-1.5 0m3-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0m1.5 0a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0"></path>
                              </svg>';
    }
    
    $features[] = [
        'icon' => $transmission_icon,
        'text' => ucfirst($car['transmission'])
    ];
    
    // Fuel type icon
    if ($car['fuel_type'] === 'electric') {
        $fuel_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M14.69 2.21L4.33 11.49c-.64.58-.28 1.65.58 1.73L8 13.64V19c0 .55.45 1 1 1s1-.45 1-1v-6.14L12.96 11l1.06-2.23L16.31 7l5.74-.5c.86-.08 1.22-1.15.58-1.73L12.27.21c-.39-.35-.98-.35-1.37 0L9.69 2.21c-.64.58-.28 1.65.58 1.73z"/>
                      </svg>';
    } elseif ($car['fuel_type'] === 'hybrid') {
        $fuel_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M19 12h-2l3.35-6.71c.12-.25.15-.56.07-.83-.08-.27-.26-.5-.51-.65C19.66 3.66 19.34 3.66 19.09 3.81L16 6V3c0-.55-.45-1-1-1s-1 .45-1 1v3.17L10.55 4.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89v4.38L6.45 8.9c-.29-.19-.66-.19-.95 0-.29.19-.48.52-.48.89V12H3c-.55 0-1 .45-1 1s.45 1 1 1h2v2.21c0 .37.19.7.48.89.29.19.66.19.95 0L9.12 15.83V18c0 .37.19.7.48.89.14.09.3.14.47.14s.33-.05.47-.14c.29-.19.48-.52.48-.89v-2.17L14 17.1c.29.19.66.19.95 0 .29-.19.48-.52.48-.89V14h2c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                      </svg>';
    } else {
        $fuel_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,2A1,1 0 0,0 2,3V19A3,3 0 0,0 5,22H11A3,3 0 0,0 14,19V16H16V19A1,1 0 0,0 17,20H18A1,1 0 0,0 19,19V12A1,1 0 0,0 18,11H17A1,1 0 0,0 16,12V14H14V3A1,1 0 0,0 13,2H3M4,4H12V12H4V4M6,6V10H10V6H6Z"/>
                      </svg>';
    }
    
    $features[] = [
        'icon' => $fuel_icon,
        'text' => ucfirst(str_replace('-', ' ', $car['fuel_type']))
    ];
    
    return $features;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars | <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/icons/favicon.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/browse.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/partials/header.php'; ?>
    
    <main>
        <section class="browse-section">
            <div class="container">
                <form method="GET" action="<?php echo BASE_URL; ?>/browse" class="search-filters">
                    <div class="search-bar">
                        <div class="search-input-container">
                            <span class="search-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input type="text" name="search" class="search-input" placeholder="Search by car make/model or location (e.g., Tesla, Bole)..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <button type="button" id="filter-toggle-btn" class="filter-toggle">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                        </button>
                    </div>
                    
                    <div id="filters-container" class="filters-container">
                        <div class="filter-group">
                            <label class="filter-label">Vehicle Type</label>
                            <select name="vehicle_type" class="filter-select">
                                <option value="">All Types</option>
                                <option value="sedan" <?php echo ($filters['vehicle_type'] == 'sedan') ? 'selected' : ''; ?>>Sedan</option>
                                <option value="suv" <?php echo ($filters['vehicle_type'] == 'suv') ? 'selected' : ''; ?>>SUV</option>
                                <option value="truck" <?php echo ($filters['vehicle_type'] == 'truck') ? 'selected' : ''; ?>>Truck</option>
                                <option value="van" <?php echo ($filters['vehicle_type'] == 'van') ? 'selected' : ''; ?>>Van</option>
                                <option value="convertible" <?php echo ($filters['vehicle_type'] == 'convertible') ? 'selected' : ''; ?>>Convertible</option>
                                <option value="coupe" <?php echo ($filters['vehicle_type'] == 'coupe') ? 'selected' : ''; ?>>Coupe</option>
                                <option value="hatchback" <?php echo ($filters['vehicle_type'] == 'hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Make</label>
                            <select name="make" class="filter-select">
                                <option value="">All Makes</option>
                                <option value="toyota" <?php echo ($filters['make'] == 'toyota') ? 'selected' : ''; ?>>Toyota</option>
                                <option value="honda" <?php echo ($filters['make'] == 'honda') ? 'selected' : ''; ?>>Honda</option>
                                <option value="tesla" <?php echo ($filters['make'] == 'tesla') ? 'selected' : ''; ?>>Tesla</option>
                                <option value="bmw" <?php echo ($filters['make'] == 'bmw') ? 'selected' : ''; ?>>BMW</option>
                                <option value="mercedes" <?php echo ($filters['make'] == 'mercedes') ? 'selected' : ''; ?>>Mercedes-Benz</option>
                                <option value="ford" <?php echo ($filters['make'] == 'ford') ? 'selected' : ''; ?>>Ford</option>
                                <option value="chevrolet" <?php echo ($filters['make'] == 'chevrolet') ? 'selected' : ''; ?>>Chevrolet</option>
                                <option value="audi" <?php echo ($filters['make'] == 'audi') ? 'selected' : ''; ?>>Audi</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Transmission</label>
                            <select name="transmission" class="filter-select">
                                <option value="">Any</option>
                                <option value="automatic" <?php echo ($filters['transmission'] == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                                <option value="manual" <?php echo ($filters['transmission'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Fuel Type</label>
                            <select name="fuel_type" class="filter-select">
                                <option value="">Any</option>
                                <option value="gasoline" <?php echo ($filters['fuel_type'] == 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                                <option value="diesel" <?php echo ($filters['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                <option value="electric" <?php echo ($filters['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                                <option value="hybrid" <?php echo ($filters['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Price Range (per day)</label>
                            <div class="price-range">
                                <input type="number" name="min_price" class="price-input" placeholder="Min" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                                <span>to</span>
                                <input type="number" name="max_price" class="price-input" placeholder="Max" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Seating Capacity</label>
                            <select name="seating" class="filter-select">
                                <option value="">Any</option>
                                <option value="2" <?php echo ($filters['seating'] == '2') ? 'selected' : ''; ?>>2 seats</option>
                                <option value="4" <?php echo ($filters['seating'] == '4') ? 'selected' : ''; ?>>4 seats</option>
                                <option value="5" <?php echo ($filters['seating'] == '5') ? 'selected' : ''; ?>>5 seats</option>
                                <option value="7" <?php echo ($filters['seating'] == '7') ? 'selected' : ''; ?>>7 seats</option>
                                <option value="8" <?php echo ($filters['seating'] == '8') ? 'selected' : ''; ?>>8+ seats</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Available From</label>
                            <input type="date" name="available_from" class="filter-input" value="<?php echo htmlspecialchars($filters['available_from']); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Available Until</label>
                            <input type="date" name="available_to" class="filter-input" value="<?php echo htmlspecialchars($filters['available_to']); ?>">
                        </div>
                        
                        <div class="filter-actions">
                            <a href="<?php echo BASE_URL; ?>/browse" class="reset-btn">Reset</a>
                            <button type="submit" class="apply-btn">Apply Filters</button>
                        </div>
                    </div>
                </form>
                
                <div class="browse-header">
                    <div class="results-count">
                        Showing <strong><?php echo count($cars); ?></strong> of <strong><?php echo $total_cars; ?></strong> cars
                    </div>
                    
                    <form method="GET" action="<?php echo BASE_URL; ?>/browse" class="results-sort">
                        <!-- Preserve existing filters -->
                        <?php foreach ($_GET as $key => $value): ?>
                            <?php if ($key !== 'sort' && !empty($value)): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <span class="sort-label">Sort by:</span>
                        <select name="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Newest Listings</option>
                            <option value="price-low" <?php echo ($sort_by == 'price-low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo ($sort_by == 'price-high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo ($sort_by == 'rating') ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </form>
                </div>
                
                <?php if (empty($cars)): ?>
                    <div class="empty-state">
                        <h3>No cars found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                    </div>
                <?php else: ?>
                    <div class="cars-grid">
                        <?php foreach ($cars as $car): ?>
                            <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                                <div class="car-image">
                                    <?php if (time() - strtotime($car['created_at']) < 7 * 24 * 60 * 60): // New if less than 7 days old ?>
                                        <div class="car-badge">New</div>
                                    <?php endif; ?>
                                    <button class="car-like" data-car-id="<?php echo $car['id']; ?>">❤</button>
                                    <img src="<?php echo getCarImageUrl($car['primary_image']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                </div>
                                <div class="car-details">
                                    <h3 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                                    <p class="car-type"><?php echo ucfirst(str_replace('_', ' ', $car['vehicle_type'])); ?></p>
                                    
                                    <div class="car-features">
                                        <?php $features = getCarFeatures($car); ?>
                                        <?php foreach ($features as $feature): ?>
                                            <div class="car-feature">
                                                <?php echo $feature['icon']; ?>
                                                <span class="car-feature-text"><?php echo htmlspecialchars($feature['text']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
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
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo BASE_URL; ?>/browse?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <div class="page-item disabled">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="<?php echo BASE_URL; ?>/browse?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo BASE_URL; ?>/browse?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <div class="page-item disabled">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../includes/partials/footer.php'; ?>
    
    <script>
        // Pass PHP variables to JavaScript
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/browse.js"></script>
</body>
</html>