<?php
header("Content-Type: application/json");
require 'config.php';

if (!isset($_POST['user_email']) || !isset($_POST['status'])) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$user_email = $_POST['user_email'];
$status = strtolower($_POST['status']);
$booking_id = !empty($_POST['booking_id']) ? $_POST['booking_id'] : null;
$emergency_id = !empty($_POST['emergency_id']) ? $_POST['emergency_id'] : null;

$valid_statuses = ['pending', 'in progress', 'approved', 'declined', 'completed'];

if (!in_array($status, $valid_statuses)) {
    echo json_encode(["error" => "Invalid status provided"]);
    exit;
}

// Debugging: Log received data
error_log("Received Data: " . json_encode($_POST));

try {
    // Ensure at least one ID is provided
    if (!$booking_id && !$emergency_id) {
        echo json_encode(["error" => "Either booking_id or emergency_id is required"]);
        exit;
    }

    // Check if a notification already exists for this booking or emergency status
    $check_sql = "SELECT id FROM notifications WHERE user_email = :user_email AND status = :status 
                  AND ((booking_id IS NOT NULL AND booking_id = :booking_id) 
                  OR (emergency_id IS NOT NULL AND emergency_id = :emergency_id))";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $check_stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $check_stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':emergency_id', $emergency_id, PDO::PARAM_INT);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "Notification already exists"]);
        exit;
    } else {
        echo json_encode(["debug" => "No duplicate found, proceeding to insert"]);
    }

    // Insert new notification
    $sql = "INSERT INTO notifications (booking_id, emergency_id, user_email, status, is_read) 
            VALUES (:booking_id, :emergency_id, :user_email, :status, 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':emergency_id', $emergency_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Notification created successfully"]);
    } else {
        echo json_encode(["error" => "Failed to insert notification"]);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
