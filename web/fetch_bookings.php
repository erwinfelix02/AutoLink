<?php
require 'config.php';

header("Content-Type: application/json");

try {
    // Get serviceType from GET request, default to 'all'
    $serviceType = isset($_GET['serviceType']) ? $_GET['serviceType'] : 'all';

    if ($serviceType === 'all') {
        $query = "SELECT booking_id, user_id, user_name, user_email, service_name, service_price, 
                         service_description, booking_date, booking_time, selected_vehicle, inclusions, created_at, status 
                  FROM bookings ORDER BY booking_date DESC, booking_time ASC";
        $stmt = $pdo->query($query);
    } else {
        $query = "SELECT booking_id, user_id, user_name, user_email, service_name, service_price, 
                         service_description, booking_date, booking_time, selected_vehicle, inclusions, created_at, status 
                  FROM bookings WHERE service_name = ? 
                  ORDER BY booking_date DESC, booking_time ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$serviceType]);
    }

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
