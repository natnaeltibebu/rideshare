<?php
require_once __DIR__ . '/../config/database.php';

class Booking {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create($data) {
        // Debug logging
        error_log("Booking::create called with data: " . print_r($data, true));
        
        $query = "INSERT INTO bookings (user_id, car_id, start_date, end_date, total_amount, 
                  security_deposit, pickup_location, notes) 
                  VALUES (:user_id, :car_id, :start_date, :end_date, :total_amount, 
                  :security_deposit, :pickup_location, :notes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Ensure all required fields have values
        $booking_data = [
            'user_id' => $data['user_id'] ?? 1,
            'car_id' => $data['car_id'] ?? 0,
            'start_date' => $data['start_date'] ?? '',
            'end_date' => $data['end_date'] ?? '',
            'total_amount' => $data['total_amount'] ?? 0,
            'security_deposit' => $data['security_deposit'] ?? 0,
            'pickup_location' => $data['pickup_location'] ?? '',
            'notes' => $data['notes'] ?? ''
        ];
        
        error_log("Prepared booking data: " . print_r($booking_data, true));
        
        // Bind all parameters explicitly
        $stmt->bindValue(':user_id', $booking_data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':car_id', $booking_data['car_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $booking_data['start_date'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $booking_data['end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':total_amount', $booking_data['total_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':security_deposit', $booking_data['security_deposit'], PDO::PARAM_STR);
        $stmt->bindValue(':pickup_location', $booking_data['pickup_location'], PDO::PARAM_STR);
        $stmt->bindValue(':notes', $booking_data['notes'], PDO::PARAM_STR);
        
        try {
            if ($stmt->execute()) {
                $booking_id = $this->conn->lastInsertId();
                error_log("Booking created successfully with ID: " . $booking_id);
                return $booking_id;
            } else {
                error_log("Booking creation failed - execute returned false");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (Exception $e) {
            error_log("Booking creation exception: " . $e->getMessage());
            return false;
        }
    }
    
    // ... rest of the methods stay the same
    
    public function findById($id) {
        $query = "SELECT b.*, c.make, c.model, c.year, c.vehicle_type, 
                  u.first_name, u.last_name, u.email, u.phone,
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN users u ON b.user_id = u.id 
                  WHERE b.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getByUserId($user_id, $limit = 10, $offset = 0) {
        $query = "SELECT b.*, c.make, c.model, c.year, c.vehicle_type,
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY b.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getByCarOwnerId($owner_id, $limit = 10, $offset = 0) {
        $query = "SELECT b.*, c.make, c.model, c.year, c.vehicle_type,
                  u.first_name, u.last_name, u.email, u.phone,
                  (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as car_image
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN users u ON b.user_id = u.id 
                  WHERE c.user_id = :owner_id 
                  ORDER BY b.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE bookings SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function getCarBookings($car_id, $start_date = null, $end_date = null) {
        $query = "SELECT * FROM bookings WHERE car_id = :car_id AND status IN ('confirmed', 'active')";
        $params = [':car_id' => $car_id];
        
        if ($start_date && $end_date) {
            $query .= " AND (
                (start_date <= :start_date AND end_date >= :start_date) OR
                (start_date <= :end_date AND end_date >= :end_date) OR
                (start_date >= :start_date AND end_date <= :end_date)
            )";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>