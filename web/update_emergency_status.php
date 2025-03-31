<?php
require 'config.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["emergency_id"])) {
    echo json_encode(["success" => false, "message" => "Missing emergency_id."]);
    exit;
}

$emergency_id = $data["emergency_id"];
$status = isset($data["status"]) ? $data["status"] : null;
$price = isset($data["price"]) ? $data["price"] : null;
$other_info = isset($data["other_info"]) ? $data["other_info"] : null;

try {
    // ✅ If only status is being updated (No price provided)
    if ($status && is_null($price)) {
        $stmt = $pdo->prepare("UPDATE emergency_service SET status = :status WHERE emergency_id = :emergency_id");
        $stmt->execute([
            ":status" => $status,
            ":emergency_id" => $emergency_id
        ]);
        $message = "Status updated successfully.";
    
    // ✅ If price and other_info are being updated (price is provided)
    } elseif (!is_null($price)) {
        $stmt = $pdo->prepare("UPDATE emergency_service SET price = :price, other_info = :other_info WHERE emergency_id = :emergency_id");
        $stmt->execute([
            ":price" => $price,
            ":other_info" => $other_info,
            ":emergency_id" => $emergency_id
        ]);
        $message = "Price updated successfully.";
    
    } else {
        echo json_encode(["success" => false, "message" => "No valid update fields provided."]);
        exit;
    }

    // ✅ If update was successful
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => $message]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
