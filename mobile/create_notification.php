<?php
header("Content-Type: application/json");
require 'config.php';

if (!isset($_POST['booking_id']) || !isset($_POST['user_email']) || !isset($_POST['status'])) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$booking_id = $_POST['booking_id'];
$user_email = $_POST['user_email'];
$status = strtolower($_POST['status']);

$valid_statuses = ['pending', 'in progress', 'approved', 'declined', 'completed'];

if (!in_array($status, $valid_statuses)) {
    echo json_encode(["error" => "Invalid status provided"]);
    exit;
}

try {
    // Check if notification already exists for this booking status
    $check_sql = "SELECT id FROM notifications WHERE booking_id = :booking_id AND user_email = :user_email AND status = :status";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $check_stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "Notification already exists"]);
        exit;
    }

    // Insert new notification
    $sql = "INSERT INTO notifications (booking_id, user_email, status, is_read) 
            VALUES (:booking_id, :user_email, :status, 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Notification created successfully"]);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
