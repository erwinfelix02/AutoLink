<?php
header("Content-Type: application/json");
require 'config.php';

if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
    echo json_encode(["error" => "Missing or empty notification_id parameter"]);
    exit;
}

$notification_id = $_POST['notification_id'];

try {
    $sql = "DELETE FROM notifications WHERE id = :notification_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Notification deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Notification not found"]);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
