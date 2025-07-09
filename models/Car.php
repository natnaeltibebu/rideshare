<?php
// models/Car.php (Updated version)
require_once __DIR__ . '/../config/database.php';

class Car {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create($data) {
        $query = "INSERT INTO cars (user_id, make, model, year, vehicle_type, transmission, 
                  fuel_type, seating_capacity, price_per_day, security_deposit, pickup_location, 
                  description, available_from, available_to) 
                  VALUES (:user_id, :make, :model, :year, :vehicle_type, :transmission, 
                  :fuel_type, :seating_capacity, :price_per_day, :security_deposit, 
                  :pickup_location, :description, :available_from, :available_to)";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT c.*, u.first_name, u.last_name, u.phone,
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
                  FROM cars c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getAll($filters = [], $limit = CARS_PER_PAGE, $offset = 0, $order_by = 'c.created_at DESC') {
        $where_conditions = ["c.status = 'active'"];
        $params = [];
        
        if (!empty($filters['make'])) {
            $where_conditions[] = "c.make LIKE :make";
            $params[':make'] = '%' . $filters['make'] . '%';
        }
        
        if (!empty($filters['model'])) {
            $where_conditions[] = "c.model LIKE :model";
            $params[':model'] = '%' . $filters['model'] . '%';
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
        
        if (!empty($filters['seating'])) {
            $where_conditions[] = "c.seating_capacity = :seating";
            $params[':seating'] = $filters['seating'];
        }
        
        if (!empty($filters['available_from'])) {
            $where_conditions[] = "c.available_from <= :available_from";
            $params[':available_from'] = $filters['available_from'];
        }
        
        if (!empty($filters['available_to'])) {
            $where_conditions[] = "c.available_to >= :available_to";
            $params[':available_to'] = $filters['available_to'];
        }
        
        $query = "SELECT c.*, 
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
                  FROM cars c 
                  WHERE " . implode(' AND ', $where_conditions) . "
                  ORDER BY " . $order_by . " 
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
    
    public function getTotalCount($filters = []) {
        $where_conditions = ["c.status = 'active'"];
        $params = [];
        
        if (!empty($filters['make'])) {
            $where_conditions[] = "c.make LIKE :make";
            $params[':make'] = '%' . $filters['make'] . '%';
        }
        
        if (!empty($filters['model'])) {
            $where_conditions[] = "c.model LIKE :model";
            $params[':model'] = '%' . $filters['model'] . '%';
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
        
        if (!empty($filters['seating'])) {
            $where_conditions[] = "c.seating_capacity = :seating";
            $params[':seating'] = $filters['seating'];
        }
        
        if (!empty($filters['available_from'])) {
            $where_conditions[] = "c.available_from <= :available_from";
            $params[':available_from'] = $filters['available_from'];
        }
        
        if (!empty($filters['available_to'])) {
            $where_conditions[] = "c.available_to >= :available_to";
            $params[':available_to'] = $filters['available_to'];
        }
        
        $query = "SELECT COUNT(*) as total FROM cars c WHERE " . implode(' AND ', $where_conditions);
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    public function getByUserId($user_id) {
        $query = "SELECT c.*, 
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image
                  FROM cars c 
                  WHERE c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function addImage($car_id, $image_path, $is_primary = false) {
        // If this is primary, remove primary flag from other images
        if ($is_primary) {
            $update_query = "UPDATE car_images SET is_primary = 0 WHERE car_id = :car_id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':car_id', $car_id);
            $update_stmt->execute();
        }
        
        $query = "INSERT INTO car_images (car_id, image_path, is_primary) VALUES (:car_id, :image_path, :is_primary)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':car_id', $car_id);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':is_primary', $is_primary, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    
    public function getImages($car_id) {
        $query = "SELECT * FROM car_images WHERE car_id = :car_id ORDER BY is_primary DESC, created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':car_id', $car_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $query = "UPDATE cars SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $query = "DELETE FROM cars WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function isAvailable($car_id, $start_date, $end_date) {
        // Debug logging
        error_log("Checking availability for car_id: $car_id, dates: $start_date to $end_date");
        
        // First, check if the car exists and get its availability window
        $car_query = "SELECT available_from, available_to FROM cars WHERE id = :car_id AND status = 'active'";
        $car_stmt = $this->conn->prepare($car_query);
        $car_stmt->bindValue(':car_id', $car_id);
        $car_stmt->execute();
        $car = $car_stmt->fetch();
        
        if (!$car) {
            error_log("Car not found or not active");
            return false;
        }
        
        // Check if requested dates fall within the car's availability window
        if ($car['available_from'] && $start_date < $car['available_from']) {
            error_log("Start date {$start_date} is before car's available_from {$car['available_from']}");
            return false;
        }
        
        if ($car['available_to'] && $end_date > $car['available_to']) {
            error_log("End date {$end_date} is after car's available_to {$car['available_to']}");
            return false;
        }
        
        // Now check for conflicting bookings
        $booking_query = "SELECT COUNT(*) as booking_count FROM bookings 
                  WHERE car_id = :car_id 
                  AND status IN ('confirmed', 'active') 
                  AND (
                      (start_date <= :start_date1 AND end_date >= :start_date2) OR
                      (start_date <= :end_date1 AND end_date >= :end_date2) OR
                      (start_date >= :start_date3 AND end_date <= :end_date3)
                  )";
        
        $booking_stmt = $this->conn->prepare($booking_query);
        $booking_stmt->bindValue(':car_id', $car_id);
        $booking_stmt->bindValue(':start_date1', $start_date);
        $booking_stmt->bindValue(':start_date2', $start_date);
        $booking_stmt->bindValue(':start_date3', $start_date);
        $booking_stmt->bindValue(':end_date1', $end_date);
        $booking_stmt->bindValue(':end_date2', $end_date);
        $booking_stmt->bindValue(':end_date3', $end_date);
        
        try {
            $booking_stmt->execute();
            $result = $booking_stmt->fetch();
            $booking_count = $result['booking_count'];
            $available = ($booking_count == 0);
            
            error_log("Availability window check: available_from={$car['available_from']}, available_to={$car['available_to']}");
            error_log("Booking conflict check: $booking_count conflicting bookings, available: " . ($available ? 'yes' : 'no'));
            return $available;
        } catch (Exception $e) {
            error_log("Error checking availability: " . $e->getMessage());
            return false; // Assume not available if there's an error
        }
    }
}
?>