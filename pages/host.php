<?php
// Add this to the top of your host.php file after the existing requires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login to access this page
require_login();
$current_page = 'host';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_listing'])) {
    try {
        // Debug output
        error_log("Form submitted with data: " . print_r($_POST, true));
        error_log("Files submitted: " . print_r($_FILES, true));
        
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token. Please refresh the page and try again.');
        }

        $controller = new CarController();
        
        // Map form field names to expected backend field names
        $mapped_data = [
            'user_id' => $_SESSION['user_id'],
            'make' => sanitize_input($_POST['make_search'] ?? $_POST['make'] ?? ''),
            'model' => sanitize_input($_POST['model'] ?? ''),
            'year' => (int)($_POST['year'] ?? 0),
            'vehicle_type' => sanitize_input($_POST['vehicle_type'] ?? ''),
            'transmission' => sanitize_input($_POST['transmission'] ?? ''),
            'fuel_type' => sanitize_input($_POST['fuel_type'] ?? ''),
            'seating_capacity' => (int)str_replace([' seats', ' seat'], '', $_POST['seating'] ?? '0'),
            'price_per_day' => (float)($_POST['price'] ?? 0),
            'security_deposit' => (float)($_POST['security_deposit'] ?? $_POST['deposit'] ?? 0),
            'pickup_location' => sanitize_input($_POST['location'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'available_from' => $_POST['available-from'] ?? null,
            'available_to' => $_POST['available-to'] ?? null,
            'contact' => sanitize_input($_POST['contact'] ?? '')
        ];
        
        // Handle the car creation
        $car_id = createCarListing($mapped_data, $_FILES);
        
        if ($car_id) {
            $success_message = "Your car has been listed successfully! Our team will review your listing within 24 hours.";
            $show_success = true;
        } else {
            throw new Exception('Failed to create car listing. Please try again.');
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Car listing error: " . $e->getMessage());
    }
}

// Add the createCarListing function here (from above)
function createCarListing($data, $files) {
    try {
        $car = new Car();
        
        // Debug: Log the received data
        error_log("Processing car listing with data: " . print_r($data, true));
        error_log("Files received: " . print_r($files, true));
        
        // Validate required fields
        $required_fields = ['make', 'model', 'year', 'vehicle_type', 'transmission', 'fuel_type', 'seating_capacity', 'price_per_day', 'security_deposit', 'pickup_location', 'description'];
        
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
        }
        
        // Validate data types
        if (!is_numeric($data['year']) || $data['year'] < 1990 || $data['year'] > (date('Y') + 1)) {
            throw new Exception('Invalid year: ' . $data['year']);
        }
        
        if (!is_numeric($data['price_per_day']) || $data['price_per_day'] <= 0) {
            throw new Exception('Invalid price per day: ' . $data['price_per_day']);
        }
        
        if (!is_numeric($data['security_deposit']) || $data['security_deposit'] < 0) {
            throw new Exception('Invalid security deposit: ' . $data['security_deposit']);
        }
        
        if (strlen(trim($data['description'])) < 50) {
            throw new Exception('Description must be at least 50 characters. Current length: ' . strlen(trim($data['description'])));
        }
        
        // Prepare car data for database
        $car_data = [
            'user_id' => $data['user_id'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => (int)$data['year'],
            'vehicle_type' => $data['vehicle_type'],
            'transmission' => $data['transmission'],
            'fuel_type' => $data['fuel_type'],
            'seating_capacity' => (int)$data['seating_capacity'],
            'price_per_day' => (float)$data['price_per_day'],
            'security_deposit' => (float)$data['security_deposit'],
            'pickup_location' => $data['pickup_location'],
            'description' => $data['description'],
            'available_from' => $data['available_from'],
            'available_to' => $data['available_to']
        ];
        
        // Create car record
        $car_id = $car->create($car_data);
        
        if (!$car_id) {
            throw new Exception('Failed to create car record in database');
        }
        
        error_log("Car created successfully with ID: " . $car_id);
        
        // Handle image uploads - Fixed multiple image handling
        if (isset($files['car-photos'])) {
            $successful_uploads = 0;
            
            // Check if we have multiple files or single file
            if (is_array($files['car-photos']['name'])) {
                // Multiple files
                $upload_count = count($files['car-photos']['name']);
                error_log("Processing $upload_count photo uploads (multiple files)");
                
                for ($i = 0; $i < $upload_count; $i++) {
                    if ($files['car-photos']['error'][$i] === UPLOAD_ERR_OK && !empty($files['car-photos']['name'][$i])) {
                        $file = [
                            'name' => $files['car-photos']['name'][$i],
                            'type' => $files['car-photos']['type'][$i],
                            'tmp_name' => $files['car-photos']['tmp_name'][$i],
                            'error' => $files['car-photos']['error'][$i],
                            'size' => $files['car-photos']['size'][$i]
                        ];
                        
                        try {
                            $image_path = upload_file($file, 'cars');
                            $is_primary = ($successful_uploads === 0); // First successful upload is primary
                            $result = $car->addImage($car_id, $image_path, $is_primary);
                            
                            if ($result) {
                                $successful_uploads++;
                                error_log("Successfully uploaded image $i: " . $image_path . " (Primary: " . ($is_primary ? 'Yes' : 'No') . ")");
                            } else {
                                error_log("Database insert failed for image: " . $image_path);
                            }
                        } catch (Exception $e) {
                            error_log("Image upload failed for file $i: " . $e->getMessage());
                        }
                    } else {
                        if (!empty($files['car-photos']['name'][$i])) {
                            error_log("Skipping file $i - Error: " . $files['car-photos']['error'][$i] . ", Name: " . $files['car-photos']['name'][$i]);
                        }
                    }
                }
            } else {
                // Single file
                error_log("Processing single photo upload");
                
                if ($files['car-photos']['error'] === UPLOAD_ERR_OK && !empty($files['car-photos']['name'])) {
                    try {
                        $image_path = upload_file($files['car-photos'], 'cars');
                        $result = $car->addImage($car_id, $image_path, true); // Single image is primary
                        
                        if ($result) {
                            $successful_uploads++;
                            error_log("Successfully uploaded single image: " . $image_path);
                        } else {
                            error_log("Database insert failed for single image: " . $image_path);
                        }
                    } catch (Exception $e) {
                        error_log("Single image upload failed: " . $e->getMessage());
                    }
                }
            }
            
            error_log("Total successful uploads: " . $successful_uploads);
            
            if ($successful_uploads === 0) {
                error_log("Warning: No images were uploaded successfully");
            }
        } else {
            error_log("No car-photos field found in files array");
        }
        
        return $car_id;
        
    } catch (Exception $e) {
        error_log("Error in createCarListing: " . $e->getMessage());
        throw $e;
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Get current user info
$current_user = get_logged_in_user();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rideshare | List Your Car</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/icons/favicon.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/host.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/partials/header.php'; ?>
    
    <main>
        <section class="page-header">
            <div class="container">
                <div class="header-content">
                    <div class="page-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                        Share Your Vehicle
                    </div>
                    <h1 class="page-title">List Your Car on Rideshare</h1>
                    <p class="subtitle">Turn your car into a passive income stream. Create your listing in just a few minutes and start earning.</p>
                </div>
            </div>
        </section>
        
        <section class="form-section">
            <div class="container">
                <div class="form-container">
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-header">
                        <h2 class="form-header-title">Car Listing Details</h2>
                    </div>
                    
                    <div class="form-body">
                        <?php if (!isset($show_success)): ?>
                        <div class="form-progress">
                            <div class="progress-step active">
                                <div class="step-icon">1</div>
                                <div class="step-text">Vehicle Details</div>
                            </div>
                            <div class="progress-step">
                                <div class="step-icon">2</div>
                                <div class="step-text">Rental Information</div>
                            </div>
                            <div class="progress-step">
                                <div class="step-icon">3</div>
                                <div class="step-text">Additional Details</div>
                            </div>
                            <div class="progress-step">
                                <div class="step-icon">4</div>
                                <div class="step-text">Review</div>
                            </div>
                        </div>
                        
                        <form id="listing-form" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="submit_listing" value="1">
                            
                            <!-- Step 1: Vehicle Details -->
                            <div class="form-step active" id="step-1">
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="form-label" for="make_search">Make</label>
                                        <div class="autocomplete-wrapper">
                                            <input type="text" class="form-input" id="make-search" name="make_search" placeholder="Search for a make..." autocomplete="off" value="<?php echo htmlspecialchars($_POST['make_search'] ?? ''); ?>">
                                            <div class="autocomplete-results" id="make-results"></div>
                                        </div>
                                        <div class="error-message" id="make-error">Please select a valid car make</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="model">Model</label>
                                        <select class="form-select" id="model" name="model" required>
                                            <option value="" selected disabled>Select make first</option>
                                        </select>
                                        <div class="error-message" id="model-error">Please select a model</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="year">Year</label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="" selected disabled>Select year</option>
                                            <?php for ($year = 2025; $year >= 2006; $year--): ?>
                                                <option value="<?php echo $year; ?>" <?php echo (($_POST['year'] ?? '') == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="error-message" id="year-error">Please select a year</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="vehicle-type">Vehicle Type</label>
                                        <select class="form-select" id="vehicle-type" name="vehicle_type" required>
                                            <option value="" selected disabled>Select vehicle type</option>
                                            <option value="sedan" <?php echo (($_POST['vehicle_type'] ?? '') == 'sedan') ? 'selected' : ''; ?>>Sedan</option>
                                            <option value="suv" <?php echo (($_POST['vehicle_type'] ?? '') == 'suv') ? 'selected' : ''; ?>>SUV</option>
                                            <option value="truck" <?php echo (($_POST['vehicle_type'] ?? '') == 'truck') ? 'selected' : ''; ?>>Truck</option>
                                            <option value="van" <?php echo (($_POST['vehicle_type'] ?? '') == 'van') ? 'selected' : ''; ?>>Van</option>
                                            <option value="convertible" <?php echo (($_POST['vehicle_type'] ?? '') == 'convertible') ? 'selected' : ''; ?>>Convertible</option>
                                            <option value="coupe" <?php echo (($_POST['vehicle_type'] ?? '') == 'coupe') ? 'selected' : ''; ?>>Coupe</option>
                                            <option value="hatchback" <?php echo (($_POST['vehicle_type'] ?? '') == 'hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                                            <option value="wagon" <?php echo (($_POST['vehicle_type'] ?? '') == 'wagon') ? 'selected' : ''; ?>>Wagon</option>
                                        </select>
                                        <div class="error-message" id="vehicle-type-error">Please select a vehicle type</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="transmission">Transmission</label>
                                        <select class="form-select" id="transmission" name="transmission" required>
                                            <option value="" selected disabled>Select transmission</option>
                                            <option value="automatic" <?php echo (($_POST['transmission'] ?? '') == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                                            <option value="manual" <?php echo (($_POST['transmission'] ?? '') == 'manual') ? 'selected' : ''; ?>>Manual</option>
                                            <option value="cvt" <?php echo (($_POST['transmission'] ?? '') == 'cvt') ? 'selected' : ''; ?>>CVT</option>
                                            <option value="semi-automatic" <?php echo (($_POST['transmission'] ?? '') == 'semi-automatic') ? 'selected' : ''; ?>>Semi-Automatic</option>
                                        </select>
                                        <div class="error-message" id="transmission-error">Please select a transmission type</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="fuel-type">Fuel Type</label>
                                        <select class="form-select" id="fuel-type" name="fuel_type" required>
                                            <option value="" selected disabled>Select fuel type</option>
                                            <option value="gasoline" <?php echo (($_POST['fuel_type'] ?? '') == 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                                            <option value="diesel" <?php echo (($_POST['fuel_type'] ?? '') == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                            <option value="electric" <?php echo (($_POST['fuel_type'] ?? '') == 'electric') ? 'selected' : ''; ?>>Electric</option>
                                            <option value="hybrid" <?php echo (($_POST['fuel_type'] ?? '') == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                            <option value="plug-in-hybrid" <?php echo (($_POST['fuel_type'] ?? '') == 'plug-in-hybrid') ? 'selected' : ''; ?>>Plug-in Hybrid</option>
                                            <option value="natural-gas" <?php echo (($_POST['fuel_type'] ?? '') == 'natural-gas') ? 'selected' : ''; ?>>Natural Gas</option>
                                        </select>
                                        <div class="error-message" id="fuel-type-error">Please select a fuel type</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="seating">Seating Capacity</label>
                                        <select class="form-select" id="seating" name="seating" required>
                                            <option value="" selected disabled>Select seating capacity</option>
                                            <option value="2" <?php echo (($_POST['seating'] ?? '') == '2') ? 'selected' : ''; ?>>2 seats</option>
                                            <option value="4" <?php echo (($_POST['seating'] ?? '') == '4') ? 'selected' : ''; ?>>4 seats</option>
                                            <option value="5" <?php echo (($_POST['seating'] ?? '') == '5') ? 'selected' : ''; ?>>5 seats</option>
                                            <option value="6" <?php echo (($_POST['seating'] ?? '') == '6') ? 'selected' : ''; ?>>6 seats</option>
                                            <option value="7" <?php echo (($_POST['seating'] ?? '') == '7') ? 'selected' : ''; ?>>7 seats</option>
                                            <option value="8" <?php echo (($_POST['seating'] ?? '') == '8') ? 'selected' : ''; ?>>8 seats</option>
                                            <option value="9" <?php echo (($_POST['seating'] ?? '') == '9') ? 'selected' : ''; ?>>9+ seats</option>
                                        </select>
                                        <div class="error-message" id="seating-error">Please select seating capacity</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Rental Information -->
                            <div class="form-step" id="step-2">
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="form-label" for="price">Price per Day ($)</label>
                                        <div class="price-input-wrapper">
                                            <span class="price-currency">$</span>
                                            <input type="number" class="form-input" id="price" name="price" min="1" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                                        </div>
                                        <p class="help-text">Set a competitive price to increase bookings. Average in your area: $45-60/day</p>
                                        <div class="error-message" id="price-error">Please enter a valid price</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="deposit">Security Deposit ($)</label>
                                        <div class="price-input-wrapper">
                                            <span class="price-currency">$</span>
                                            <input type="number" class="form-input" id="deposit" name="security_deposit" min="0" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($_POST['security_deposit'] ?? ''); ?>" required>
                                        </div>
                                        <p class="help-text">Refundable amount reserved at booking. Recommended: $250-500</p>
                                        <div class="error-message" id="deposit-error">Please enter a valid deposit amount</div>
                                    </div>
                                    
                                    <div class="form-field full-width">
                                        <label class="form-label" for="location">Pickup Location in Addis Ababa</label>
                                        <div class="autocomplete-wrapper">
                                            <input type="text" class="form-input" id="location" name="location" placeholder="Start typing location in Addis Ababa..." autocomplete="off" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                                            <div class="autocomplete-results" id="location-results"></div>
                                        </div>
                                        <p class="help-text">Enter a specific address or area in Addis Ababa</p>
                                        <div class="error-message" id="location-error">Please enter a valid location</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="available-from">Available From</label>
                                        <input type="date" class="form-input" id="available-from" name="available-from" value="<?php echo htmlspecialchars($_POST['available-from'] ?? ''); ?>" required>
                                        <div class="error-message" id="available-from-error">Please select a start date</div>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="available-to">Available To</label>
                                        <input type="date" class="form-input" id="available-to" name="available-to" value="<?php echo htmlspecialchars($_POST['available-to'] ?? ''); ?>" required>
                                        <div class="error-message" id="available-to-error">Please select an end date</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 3: Additional Details -->
                            <div class="form-step" id="step-3">
                                <div class="form-field">
                                    <label class="form-label">Vehicle Photos</label>
                                    <p class="help-text">Upload photos of your vehicle (optional for testing)</p>
                                    
                                    <div class="upload-container">
                                        <div class="upload-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                        </div>
                                        <h4 class="upload-text">Drag and drop photos or click to browse</h4>
                                        <p class="upload-subtext">Accepted formats: JPG, PNG (Max 10 MB)</p>
                                        <input type="file" class="upload-input" id="car-photos" name="car-photos[]" multiple accept="image/jpeg, image/png">
                                    </div>
                                    
                                    <div class="thumbnail-preview" id="photo-preview"></div>
                                    <div class="error-message" id="photos-error">Please upload at least 1 photo</div>
                                </div>
                                
                                <div class="form-field">
                                    <label class="form-label" for="description">Vehicle Description</label>
                                    <textarea class="form-textarea" id="description" name="description" placeholder="Tell renters about your car's condition, special features, and why they'll love driving it..." rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <p class="help-text">Be detailed and honest about your vehicle's condition and features (minimum 50 characters)</p>
                                    <div class="error-message" id="description-error">Please enter a description (minimum 50 characters)</div>
                                </div>
                                
                                <div class="form-field">
                                    <label class="form-label" for="contact">Owner Phone Number</label>
                                    <input type="tel" class="form-input" id="contact" name="contact" placeholder="e.g. +251 91 234 5678" value="<?php echo htmlspecialchars($_POST['contact'] ?? $current_user['phone'] ?? ''); ?>" required>
                                    <p class="help-text">This will only be shared with confirmed renters</p>
                                    <div class="error-message" id="contact-error">Please enter a valid phone number</div>
                                </div>
                                
                                <div class="form-field">
                                    <label class="form-input" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="terms" id="terms" required style="margin-top: 3px;">
                                        <span style="font-size: 0.875rem; color: var(--gray-700);">
                                            I agree to the <a href="#" style="color: var(--primary); text-decoration: none;">Terms of Service</a>, <a href="#" style="color: var(--primary); text-decoration: none;">Privacy Policy</a>, and certify that I am the legal owner of this vehicle or authorized to list it.
                                        </span>
                                    </label>
                                    <div class="error-message" id="terms-error">You must agree to the terms</div>
                                </div>
                            </div>
                            
                            <!-- Step 4: Review -->
                            <div class="form-step" id="step-4">
                                <h3 style="margin-bottom: 1.5rem; font-weight: 600; color: var(--gray-800); font-size: 1.125rem;">Review Your Listing</h3>
                                
                                <div class="review-section">
                                    <h4 class="review-section-title">Vehicle Details</h4>
                                    <div class="review-item">
                                        <div class="review-label">Make:</div>
                                        <div class="review-value" id="review-make">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Model:</div>
                                        <div class="review-value" id="review-model">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Year:</div>
                                        <div class="review-value" id="review-year">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Vehicle Type:</div>
                                        <div class="review-value" id="review-vehicle-type">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Transmission:</div>
                                        <div class="review-value" id="review-transmission">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Fuel Type:</div>
                                        <div class="review-value" id="review-fuel-type">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Seating Capacity:</div>
                                        <div class="review-value" id="review-seating">-</div>
                                    </div>
                                </div>
                                
                                <div class="review-section">
                                    <h4 class="review-section-title">Rental Information</h4>
                                    <div class="review-item">
                                        <div class="review-label">Price per Day:</div>
                                        <div class="review-value" id="review-price">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Security Deposit:</div>
                                        <div class="review-value" id="review-deposit">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Pickup Location:</div>
                                        <div class="review-value" id="review-location">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Available Dates:</div>
                                        <div class="review-value" id="review-dates">-</div>
                                    </div>
                                </div>
                                
                                <div class="review-section">
                                    <h4 class="review-section-title">Additional Details</h4>
                                    <div class="review-item">
                                        <div class="review-label">Photos:</div>
                                        <div class="review-value">
                                            <div class="review-photos" id="review-photos"></div>
                                        </div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Description:</div>
                                        <div class="review-value" id="review-description">-</div>
                                    </div>
                                    <div class="review-item">
                                        <div class="review-label">Contact Phone:</div>
                                        <div class="review-value" id="review-contact">-</div>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 1.5rem; padding: 0.875rem; background-color: var(--primary-light); border-radius: var(--radius-md); text-align: center;">
                                    <p style="color: var(--primary); font-weight: 500; font-size: 0.875rem;">Please review all details carefully before submitting your listing.</p>
                                </div>
                            </div>
                        </form>
                        
                        <?php else: ?>
                        <!-- Success Message -->
                        <div class="success-message active" id="success-message">
                            <div class="success-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <h3 class="success-title">Your car has been listed successfully!</h3>
                            <p class="success-text">Thank you for listing your car on Rideshare. Our team will review your listing within 24 hours before it goes live on our platform.</p>
                            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-primary">View My Listings</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!isset($show_success)): ?>
                    <div class="form-footer">
                        <div class="form-tip">
                            <span class="badge badge-info">Tip</span>
                            Complete listings with quality photos get 3x more bookings!
                        </div>
                        
                        <div class="form-buttons">
                            <button type="button" class="btn btn-secondary" id="prev-step" disabled>Back</button>
                            <button type="button" class="btn btn-primary" id="next-step">Continue</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php include __DIR__ . '/../includes/partials/footer.php'; ?>

    <script>
        // Pass PHP variables to JavaScript
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/host.js"></script>
</body>
</html>