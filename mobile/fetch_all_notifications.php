<?php
header("Content-Type: application/json");
require 'config.php';

// Validate user_email and read_status parameters
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

// Set the user_email and default read_status to 'all' (which means all notifications, including read and unread)
$user_email = $_POST['user_email'];
$read_status = isset($_POST['read_status']) ? $_POST['read_status'] : 'all';

// Build the SQL query to fetch notifications based on the read_status
try {
    // Base query
    $sql = "SELECT 
                n.id AS notification_id,
                n.booking_id, 
                b.service_name, 
                CASE 
                    WHEN LOWER(n.status) = 'new' THEN 'Your booking is pending approval.'  
                    WHEN LOWER(n.status) = 'pending' THEN 'Your booking is pending approval.' 
                    WHEN LOWER(n.status) = 'in progress' THEN 'Your booking is currently in progress.'
                    WHEN LOWER(n.status) = 'approved' THEN 'Your booking has been approved!'
                    WHEN LOWER(n.status) = 'declined' THEN 'Your booking has been declined.'
                    WHEN LOWER(n.status) = 'completed' THEN 'Your booking has been successfully completed!'
                    ELSE 'Unknown status'
                END AS message, 
                LOWER(n.status) AS status,  
                n.is_read,  
                DATE_FORMAT(n.created_at, '%Y-%m-%d %H:%i:%s') AS timestamp 
            FROM notifications n
            INNER JOIN bookings b ON n.booking_id = b.booking_id
            WHERE n.user_email = :user_email";
    
    // If the read_status is 'unread', add the condition to filter by unread notifications
    if ($read_status === 'unread') {
        $sql .= " AND n.is_read = 0";  // Only unread notifications
    }

    // Order notifications by created_at (most recent first)
    $sql .= " ORDER BY n.created_at DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch all notifications as an associative array
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any notifications exist and return an appropriate response
    if ($notifications) {
        echo json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        // Return a message if no notifications found
        echo json_encode(["message" => "No notifications found for this user"]);
    }

} catch (PDOException $e) {
    // Log the error and return a JSON error message
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
