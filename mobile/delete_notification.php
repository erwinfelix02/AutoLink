<?php
header("Content-Type: application/json");
require 'config.php';

try {
    // Get booking_id securely
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);

    if (!$booking_id || !is_numeric($booking_id)) {
        echo json_encode(["success" => false, "message" => "Invalid or missing booking_id"]);
        exit;
    }

    // Prepare and execute delete statement 
    $sql = "DELETE FROM notifications WHERE booking_id = :booking_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
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
