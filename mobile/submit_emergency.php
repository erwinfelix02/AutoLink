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
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $full_name = filter_var($_POST['full_name'] ?? '', FILTER_SANITIZE_STRING);
    $latitude = filter_var($_POST['latitude'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_var($_POST['longitude'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $service_needed = filter_var($_POST['service_needed'] ?? '', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($email) || empty($full_name) || empty($latitude) || empty($longitude) || empty($service_needed)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // Insert data into the database using PDO
    $stmt = $conn->prepare("INSERT INTO emergency_service (email, full_name, latitude, longitude, service_needed) 
                            VALUES (:email, :full_name, :latitude, :longitude, :service_needed)");
    
    $stmt->execute([
        ":email" => $email,
        ":full_name" => $full_name,
        ":latitude" => $latitude,
        ":longitude" => $longitude,
        ":service_needed" => $service_needed
    ]);

    echo json_encode(["success" => true, "message" => "Emergency request submitted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
