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
        $stmt = $conn->prepare("SELECT booking_id FROM bookings WHERE user_email = ? AND selected_vehicle = ? AND service_name = ?");
        $stmt->execute([$userEmail, $selectedVehicle, $serviceName]);

        echo json_encode(["exists" => $stmt->rowCount() > 0]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
