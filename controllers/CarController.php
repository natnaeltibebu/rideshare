<?php
// controllers/CarController.php - Updated for multiple image uploads
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

class CarController {
    private $car;
    
    public function __construct() {
        $this->car = new Car();
    }
    
    public function create() {
        require_login();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_response('Method not allowed', 405);
        }
        
        // Debug logging
        error_log("Form submitted with data: " . print_r($_POST, true));
        error_log("Files submitted: " . print_r($_FILES, true));
        
        $data = [
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
            'available_to' => $_POST['available-to'] ?? null
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['make'])) $errors[] = 'Make is required';
        if (empty($data['model'])) $errors[] = 'Model is required';
        if ($data['year'] < 1990 || $data['year'] > (date('Y') + 1)) $errors[] = 'Invalid year';
        if ($data['price_per_day'] <= 0) $errors[] = 'Valid price per day is required';
        if ($data['security_deposit'] < 0) $errors[] = 'Valid security deposit is required';
        if (empty($data['pickup_location'])) $errors[] = 'Pickup location is required';
        if (strlen(trim($data['description'])) < 50) $errors[] = 'Description must be at least 50 characters';
        
        if (!empty($errors)) {
            error_response(implode(', ', $errors));
        }
        
        $car_id = $this->car->create($data);
        
        if ($car_id) {
            error_log("Car created successfully with ID: " . $car_id);
            
            // Handle multiple image uploads - Fixed to use correct field name
            if (isset($_FILES['car-photos'])) {
                $successful_uploads = $this->handleImageUploads($car_id, $_FILES['car-photos']);
                error_log("Total successful uploads: " . $successful_uploads);
            }
            
            success_response(['car_id' => $car_id], 'Car listed successfully');
        } else {
            error_response('Failed to create car listing');
        }
    }
    
    private function handleImageUploads($car_id, $files) {
        $successful_uploads = 0;
        
        // Check if we have multiple files or single file
        if (is_array($files['name'])) {
            // Multiple files
            $upload_count = count($files['name']);
            error_log("Processing $upload_count photo uploads (multiple files)");
            
            for ($i = 0; $i < $upload_count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK && !empty($files['name'][$i])) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    try {
                        $image_path = upload_file($file, 'cars');
                        $is_primary = ($successful_uploads === 0); // First successful upload is primary
                        $result = $this->car->addImage($car_id, $image_path, $is_primary);
                        
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
                    if (!empty($files['name'][$i])) {
                        error_log("Skipping file $i - Error: " . $files['error'][$i] . ", Name: " . $files['name'][$i]);
                    }
                }
            }
        } else {
            // Single file
            error_log("Processing single photo upload");
            
            if ($files['error'] === UPLOAD_ERR_OK && !empty($files['name'])) {
                try {
                    $image_path = upload_file($files, 'cars');
                    $result = $this->car->addImage($car_id, $image_path, true); // Single image is primary
                    
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
        
        return $successful_uploads;
    }
    
    public function getAll($filters = [], $limit = CARS_PER_PAGE, $offset = 0) {
    $where_conditions = ["c.status = 'active'"];
    $params = [];
    
    // Handle global search - search across multiple fields with single input
    if (!empty($filters['global_search'])) {
        $search_term = '%' . $filters['global_search'] . '%';
        $where_conditions[] = "(
            c.make LIKE :global_search OR 
            c.model LIKE :global_search OR 
            CONCAT(c.make, ' ', c.model) LIKE :global_search OR
            c.pickup_location LIKE :global_search OR
            c.description LIKE :global_search OR
            c.vehicle_type LIKE :global_search
        )";
        $params[':global_search'] = $search_term;
    }
    
    if (!empty($filters['make'])) {
        $where_conditions[] = "c.make LIKE :make";
        $params[':make'] = '%' . $filters['make'] . '%';
    }
    
    if (!empty($filters['vehicle_type'])) {
        $where_conditions[] = "c.vehicle_type = :vehicle_type";
        $params[':vehicle_type'] = $filters['vehicle_type'];
    }
    
    if (!empty($filters['fuel_type'])) {
        $where_conditions[] = "c.fuel_type = :fuel_type";
        $params[':fuel_type'] = $filters['fuel_type'];
    }
    
    if (!empty($filters['transmission'])) {
        $where_conditions[] = "c.transmission = :transmission";
        $params[':transmission'] = $filters['transmission'];
    }
    
    if (!empty($filters['seating'])) {
        if ($filters['seating'] === '8+') {
            $where_conditions[] = "c.seating_capacity >= 8";
        } else {
            $where_conditions[] = "c.seating_capacity = :seating";
            $params[':seating'] = $filters['seating'];
        }
    }
    
    if (!empty($filters['min_price'])) {
        $where_conditions[] = "c.price_per_day >= :min_price";
        $params[':min_price'] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where_conditions[] = "c.price_per_day <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }
    
    if (!empty($filters['location'])) {
        $where_conditions[] = "c.pickup_location LIKE :location";
        $params[':location'] = '%' . $filters['location'] . '%';
    }
    
    if (!empty($filters['available_from']) && !empty($filters['available_to'])) {
        $where_conditions[] = "(c.available_from <= :available_from AND c.available_to >= :available_to)";
        $params[':available_from'] = $filters['available_from'];
        $params[':available_to'] = $filters['available_to'];
    }
    
    $query = "SELECT c.*, 
              (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
              FROM cars c 
              WHERE " . implode(' AND ', $where_conditions) . "
              ORDER BY c.created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $this->conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}
    
    public function getById($id) {
        $car = $this->car->findById($id);
        
        if (!$car) {
            error_response('Car not found', 404);
        }
        
        $images = $this->car->getImages($id);
        $car['images'] = $images;
        
        success_response(['car' => $car]);
    }
    
    public function getUserCars() {
        require_login();
        
        $cars = $this->car->getByUserId($_SESSION['user_id']);
        
        success_response(['cars' => $cars]);
    }
    
    public function checkAvailability() {
        $car_id = (int)($_GET['car_id'] ?? 0);
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        if (!$car_id || !$start_date || !$end_date) {
            error_response('Car ID, start date, and end date are required');
        }
        
        $available = $this->car->isAvailable($car_id, $start_date, $end_date);
        
        success_response(['available' => $available]);
    }
}
?>