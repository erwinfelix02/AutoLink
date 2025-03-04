<?php
require 'config.php'; // Include your PDO database connection

header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $fuelType = $_POST['fuel_type'] ?? '';
    $year = $_POST['year'] ?? '';
    $enginePower = $_POST['engine_power'] ?? '';
    $tankCapacity = $_POST['tank_capacity'] ?? '';
    $license = $_POST['license'] ?? '';
    $image = $_POST['image'] ?? ''; // Base64 encoded image

    // Check required fields
    if (empty($email) || empty($make) || empty($model) || empty($transmission) || empty($fuelType) || 
        empty($year) || empty($enginePower) || empty($tankCapacity) || empty($license)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $imagePath = "";
    if (!empty($image)) {
        $directory = "cars/";

        // Ensure the directory exists
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        // Generate a unique filename for the image
        $imagePath = $directory . uniqid() . ".jpg";
        file_put_contents($imagePath, base64_decode($image));
    }

    try {
        // Prepare the PDO statement
        $stmt = $conn->prepare("INSERT INTO vehicles (email, make, model, transmission, fuel_type, year, engine_power, tank_capacity, license, image_path) 
                                VALUES (:email, :make, :model, :transmission, :fuelType, :year, :enginePower, :tankCapacity, :license, :imagePath)");

        // Bind parameters
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':make', $make, PDO::PARAM_STR);
        $stmt->bindParam(':model', $model, PDO::PARAM_STR);
        $stmt->bindParam(':transmission', $transmission, PDO::PARAM_STR);
        $stmt->bindParam(':fuelType', $fuelType, PDO::PARAM_STR);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':enginePower', $enginePower, PDO::PARAM_STR);
        $stmt->bindParam(':tankCapacity', $tankCapacity, PDO::PARAM_STR);
        $stmt->bindParam(':license', $license, PDO::PARAM_STR);
        $stmt->bindParam(':imagePath', $imagePath, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Vehicle saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insert failed"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error", "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
