<?php
header("Content-Type: application/json");
require 'config.php';

try {
    // Get notification ID securely
    $notification_id = filter_input(INPUT_POST, 'notification_id', FILTER_SANITIZE_NUMBER_INT);

    // Check if notification_id is provided
    if (!$notification_id) {
        echo json_encode(["success" => false, "message" => "Notification ID is missing"]);
        exit;
    }

    // Prepare and execute delete statement based on notification_id
    $sql = "DELETE FROM notifications WHERE id = :notification_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);

    $stmt->execute();

    // Check if a row was deleted
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Notification deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Notification not found or already deleted"]);
    }
} catch (PDOException $e) {
    // Log database errors for debugging
    error_log("Database Error: " . $e->getMessage(), 0);
    echo json_encode(["success" => false, "message" => "Internal server error"]);
}
?>
