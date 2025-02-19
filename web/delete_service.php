<?php
require 'config.php'; 

header('Content-Type: application/json'); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) {
    $service_id = $input['id'];

    try {
        $stmt = $pdo->prepare("SELECT image_url FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {    
            echo json_encode(["success" => false, "message" => "Service not found."]);
            exit;
        }

        // Delete service from database
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$service_id]);

        // Remove service image if exists
        $imagePath = "../uploads/" . $service['image_url']; 
        if ($service['image_url'] && file_exists($imagePath)) {
            unlink($imagePath);
        }

        echo json_encode(["success" => true, "message" => "Service deleted successfully."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
