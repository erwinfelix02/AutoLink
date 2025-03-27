<?php
header("Content-Type: application/json");
require 'config.php';

// Validate user_email
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

$user_email = $_POST['user_email'];

// Validate notification_id
if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
    echo json_encode(["error" => "Missing or empty notification_id parameter"]);
    exit;
}

$notification_id = $_POST['notification_id'];

error_log("Received notification_id: " . $notification_id . " and user_email: " . $user_email);

try {
    // Update the notification where notification_id and user_email match, and is_read is 0
    $sql = "UPDATE notifications 
            SET is_read = 1 
            WHERE id = :notification_id 
            AND user_email = :user_email 
            AND is_read = 0
            LIMIT 1";  // Ensures only one notification is updated

    $stmt = $conn->prepare($sql);

    // Bind the notification_id and user_email to the query
    $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);

    // Log SQL execution
    error_log("Executing query: " . $sql);
    
    $stmt->execute();

    $rowCount = $stmt->rowCount();
    error_log("Update Notification: notification_id=$notification_id | Rows Updated=$rowCount");

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
