<?php
header("Content-Type: application/json");
require 'config.php';

// Validate user_email parameter
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

$userEmail = $_POST['user_email'];

try {
    // Debugging: Log the incoming request
    error_log("Fetching notifications for user_email: " . $userEmail);

    // SQL query to fetch all notifications based on the user_email
    $sql = "SELECT 
                n.id,  -- Fetch the notification ID as the primary key
                n.booking_id, 
                n.emergency_id, 
                COALESCE(b.service_name, e.service_needed) AS service_name,  -- Fetch service_name from bookings or emergency
                CASE 
                    WHEN LOWER(n.status) = 'new' THEN 'Your booking is pending approval.'  
                    WHEN LOWER(n.status) = 'pending' THEN 'Your booking is pending approval.' 
                    WHEN LOWER(n.status) = 'in progress' THEN 'Your booking is currently in progress.'
                    WHEN LOWER(n.status) = 'approved' THEN 'Your booking has been approved!'
                    WHEN LOWER(n.status) = 'declined' THEN 'Your booking has been declined.'
                    WHEN LOWER(n.status) = 'completed' THEN 'Your booking has been successfully completed!'
                    WHEN LOWER(n.status) = 'cancelled' THEN 'Your booking has been cancelled.'
                    ELSE 'Unknown status'
                END AS message, 
                LOWER(n.status) AS status,  
                n.is_read,  
                DATE_FORMAT(n.created_at, '%Y-%m-%d %H:%i:%s') AS timestamp 
            FROM notifications n
            LEFT JOIN bookings b ON n.booking_id = b.booking_id
            LEFT JOIN emergency_service e ON n.emergency_id = e.emergency_id  -- Join emergency table to get service_needed
            WHERE n.user_email = :user_email
            ORDER BY n.created_at DESC";  // Fetching all notifications for the specific user email

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_email', $userEmail, PDO::PARAM_STR);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Log the fetched notifications to ensure the correct IDs
    error_log("Fetched Notifications: " . print_r($notifications, true));

    // Return the notifications as JSON
    echo json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    // Log and return an error if there's an issue with the query
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
