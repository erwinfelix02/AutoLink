<?php
header("Content-Type: application/json");
require 'config.php';

// Ensure request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if the JSON is valid
if ($data === null) {
    echo json_encode(["success" => false, "error" => "Invalid JSON received", "raw_input" => file_get_contents("php://input")]);
    exit;
}

// Validate service ID
if (!isset($data['id']) || empty($data['id'])) {
    echo json_encode(["success" => false, "error" => "Missing service ID"]);
    exit;
}

$service_id = $data['id'];

try {
    // Prepare DELETE query
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
    $stmt->bindParam(':id', $service_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Check if any row was deleted
        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Service deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "error" => "Service not found"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete service"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
