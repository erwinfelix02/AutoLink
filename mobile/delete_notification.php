<?php
header("Content-Type: application/json");
require 'config.php';

try {
    // Get booking_id and emergency_id securely
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
    $emergency_id = filter_input(INPUT_POST, 'emergency_id', FILTER_SANITIZE_NUMBER_INT);

    // Check if either booking_id or emergency_id is provided
    if (!$booking_id && !$emergency_id) {
        echo json_encode(["success" => false, "message" => "Both booking_id and emergency_id are missing"]);
        exit;
    }

    // Prepare and execute delete statement based on available ID
    if ($booking_id) {
        $sql = "DELETE FROM notifications WHERE booking_id = :booking_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    } elseif ($emergency_id) {
        $sql = "DELETE FROM notifications WHERE emergency_id = :emergency_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':emergency_id', $emergency_id, PDO::PARAM_INT);
    }

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
