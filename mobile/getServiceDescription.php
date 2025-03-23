<?php
require 'config.php'; // Ensure this path is correct

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow API calls from any source
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get input data (supports JSON and form-data)
$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input) {
    $input = $_POST; // Fallback to form-data
}

// Debugging (remove in production)
error_log("Received input: " . print_r($input, true));

$serviceName = isset($input['name']) ? trim($input['name']) : '';

// Validate input
if (empty($serviceName)) {
    echo json_encode(["success" => false, "message" => "Invalid request, missing service name"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // First, check if the service exists in the `services` table
    $stmt = $conn->prepare("SELECT description, price FROM services WHERE name = ?");
    $stmt->execute([$serviceName]);

    // Fetch the result from the `services` table
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // If the service is found in the `services` table
        echo json_encode([
            "success" => true,
            "description" => $row['description'],
            "price" => number_format(floatval($row['price']), 2)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // If not found in `services`, check the `emergency_service` table
        $stmt = $conn->prepare("SELECT other_info AS description, price FROM emergency_service WHERE service_needed = ?");
        $stmt->execute([$serviceName]);

        // Fetch the result from the `emergency_service` table
        $emergencyRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($emergencyRow) {
            // If found in `emergency_service`, return the `other_info` as description
            echo json_encode([
                "success" => true,
                "description" => $emergencyRow['description'],
                "price" => number_format(floatval($emergencyRow['price']), 2)
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "Service not found"], JSON_UNESCAPED_UNICODE);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
