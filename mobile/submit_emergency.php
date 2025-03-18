<?php
header("Content-Type: application/json");
require 'config.php'; // Using the PDO connection

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit;
    }

    // Check if data is coming from a JSON request
    $inputData = json_decode(file_get_contents("php://input"), true);
    if ($inputData) {
        $_POST = $inputData;
    }

    // Sanitize input
    $user_email = filter_var($_POST['user_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $full_name = filter_var($_POST['full_name'] ?? '', FILTER_SANITIZE_STRING);
    $latitude = filter_var($_POST['latitude'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_var($_POST['longitude'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $service_needed = filter_var($_POST['service_needed'] ?? '', FILTER_SANITIZE_STRING);
    $vehicle = filter_var($_POST['vehicle'] ?? '', FILTER_SANITIZE_STRING);
    
    // Default status for new emergency service requests
    $status = "pending";

    // Validate required fields
    if (empty($user_email) || empty($full_name) || empty($latitude) || empty($longitude) || empty($service_needed) || empty($vehicle)) {
        echo json_encode(["success" => false, "message" => "All fields, including vehicle selection, are required"]);
        exit;
    }

    // Validate email format
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // Insert data into the database using PDO
    $stmt = $conn->prepare("INSERT INTO emergency_service (user_email, full_name, latitude, longitude, service_needed, vehicle, status) 
                            VALUES (:user_email, :full_name, :latitude, :longitude, :service_needed, :vehicle, :status)");
    
    $stmt->execute([
        ":user_email" => $user_email,
        ":full_name" => $full_name,
        ":latitude" => $latitude,
        ":longitude" => $longitude,
        ":service_needed" => $service_needed,
        ":vehicle" => $vehicle,
        ":status" => $status
    ]);

    // Get the last inserted emergency_id
    $emergency_id = $conn->lastInsertId();

    // Return the emergency_id in the response
    echo json_encode([
        "success" => true,
        "message" => "Emergency request submitted successfully",
        "emergency_id" => $emergency_id,
        "status" => $status
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage()); // Log error for debugging
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
