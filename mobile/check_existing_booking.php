<?php
require 'config.php'; // Ensure this path is correct

header("Content-Type: application/json");

// Ensure the request is GET and parameters are set
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['userEmail'], $_GET['selectedVehicle'], $_GET['serviceName'])) {
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    $userEmail = trim($_GET['userEmail']);
    $selectedVehicle = trim($_GET['selectedVehicle']);
    $serviceName = trim($_GET['serviceName']);

    try {
        // Check if a booking exists and is NOT cancelled
        $stmt = $conn->prepare("SELECT status FROM bookings WHERE user_email = ? AND selected_vehicle = ? AND service_name = ? ORDER BY booking_id DESC LIMIT 1");
        $stmt->execute([$userEmail, $selectedVehicle, $serviceName]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            if (strtolower($booking['status']) === 'cancelled') {
                echo json_encode(["exists" => false]); // Allow rebooking
            } else {
                echo json_encode(["exists" => true]);  // Booking still active
            }
        } else {
            echo json_encode(["exists" => false]); // No booking found, allow new booking
        }

    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
