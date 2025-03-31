<?php
require 'config.php'; // Database connection

header('Content-Type: application/json');

try {
    // Fetch the 3 most recent bookings
    $stmt = $pdo->query("SELECT user_name, service_name, status, booking_date, service_price 
                         FROM bookings 
                         ORDER BY created_at DESC 
                         LIMIT 3");

    $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $recentBookings]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
