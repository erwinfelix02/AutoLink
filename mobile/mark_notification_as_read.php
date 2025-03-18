<?php
header("Content-Type: application/json");
require 'config.php';

// Validate user_email
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

$user_email = $_POST['user_email'];

// Check if either booking_id or emergency_id is provided
$booking_id = isset($_POST['booking_id']) && is_numeric($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
$emergency_id = isset($_POST['emergency_id']) && is_numeric($_POST['emergency_id']) ? (int)$_POST['emergency_id'] : null;

// Ensure at least one ID is provided
if (!$booking_id && !$emergency_id) {
    echo json_encode(["error" => "Missing or invalid booking_id or emergency_id parameter"]);
    exit;
}

error_log("Received booking_id: " . $booking_id . " or emergency_id: " . $emergency_id . " and user_email: " . $user_email);

try {
    // Update notifications where either booking_id or emergency_id, user_email match and is_read is 0
    $sql = "UPDATE notifications 
            SET is_read = 1 
            WHERE (booking_id = :booking_id OR emergency_id = :emergency_id) 
            AND user_email = :user_email 
            AND is_read = 0
            LIMIT 1";  // Ensures only one notification is updated

    $stmt = $conn->prepare($sql);

    // Bind the appropriate ID (either booking_id or emergency_id) depending on what is provided
    if ($booking_id) {
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindValue(':emergency_id', null, PDO::PARAM_INT); // Set emergency_id as null
    } else {
        $stmt->bindValue(':emergency_id', $emergency_id, PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', null, PDO::PARAM_INT); // Set booking_id as null
    }

    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);

    // Log SQL execution
    error_log("Executing query: " . $sql);
    
    $stmt->execute();

    $rowCount = $stmt->rowCount();
    error_log("Update Notification: booking_id=$booking_id | emergency_id=$emergency_id | Rows Updated=$rowCount");

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
