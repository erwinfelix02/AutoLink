<?php
header("Content-Type: application/json");
require 'config.php';

// Validate user_email parameter
if (!isset($_POST['user_email']) || empty($_POST['user_email'])) {
    echo json_encode(["error" => "Missing or empty user_email parameter"]);
    exit;
}

$user_email = $_POST['user_email'];

try {
    $sql = "SELECT 
                n.id AS notification_id,
                n.booking_id, 
                b.service_name, 
                CASE 
                    WHEN LOWER(n.status) = 'new' THEN 'Your booking is pending approval.'  -- Handle 'New' as 'pending'
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
            WHERE n.user_email = :user_email AND n.is_read = 0  -- Fetch only unread notifications
            ORDER BY n.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
