<?php
header("Content-Type: application/json");
require 'config.php';

// Validate booking_id and user_email
if (!isset($_POST['booking_id']) || empty($_POST['booking_id']) || !is_numeric($_POST['booking_id'])) {
    echo json_encode(["error" => "Invalid or missing booking_id parameter"]);
    exit;
}
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

$booking_id = (int)$_POST['booking_id'];
$user_email = $_POST['user_email'];

error_log("Received booking_id: " . $booking_id . " and user_email: " . $user_email);

try {
    // Update notifications where booking_id, user_email match and is_read is 0
    $sql = "UPDATE notifications 
            SET is_read = 1 
            WHERE booking_id = :booking_id 
            AND user_email = :user_email 
            AND is_read = 0
            LIMIT 1";  // Ensures only one notification is updated

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->execute();

    $rowCount = $stmt->rowCount();
    error_log("Update Notification: booking_id=$booking_id | Rows Updated=$rowCount");

    if ($rowCount > 0) {
        echo json_encode(["success" => true, "message" => "Notification marked as read"]);
    } else {
        echo json_encode(["success" => false, "message" => "Notification already marked as read or not found"]);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
