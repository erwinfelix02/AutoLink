<?php
require 'config.php';

header("Content-Type: application/json");

try {
    $stmt = $pdo->prepare("SELECT booking_id, user_id, user_name, user_email, service_name, service_price, service_description, booking_date, booking_time, inclusions, created_at, status FROM bookings ORDER BY booking_date DESC, booking_time ASC");
    $stmt->execute();
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($bookings)) {
        echo json_encode(["success" => true, "bookings" => $bookings]);
    } else {
        echo json_encode(["success" => false, "message" => "No bookings found"]);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error fetching bookings"]);
}
?>
