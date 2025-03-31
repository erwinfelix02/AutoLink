<?php
require 'config.php';
header("Content-Type: application/json");

// Fetch the latest appointments (Bookings with status = 'New')
$appointmentsQuery = "SELECT booking_id, user_name FROM bookings WHERE status = 'New' ORDER BY created_at DESC LIMIT 5";
$appointments = $pdo->query($appointmentsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch the latest feedbacks (Unreplied feedbacks)
$feedbackQuery = "SELECT id, customer_name FROM feedback WHERE reply IS NULL ORDER BY created_at DESC LIMIT 5";
$feedbacks = $pdo->query($feedbackQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch the latest emergency service requests (Status = 'Pending')
$emergencyQuery = "SELECT emergency_id, full_name FROM emergency_service WHERE status = 'Pending' ORDER BY request_time DESC LIMIT 5";
$emergencyServices = $pdo->query($emergencyQuery)->fetchAll(PDO::FETCH_ASSOC);

// Combine all notifications
$notifications = [];

foreach ($appointments as $appointment) {
    $notifications[] = [
        "type" => "appointment",
        "message" => "📅 New appointment from {$appointment['user_name']}",
        "link" => "appointments.html?id={$appointment['booking_id']}"
    ];
}

foreach ($feedbacks as $feedback) {
    $notifications[] = [
        "type" => "feedback",
        "message" => "💬 New feedback from {$feedback['customer_name']}",
        "link" => "feedback.html?id={$feedback['id']}"
    ];
}

foreach ($emergencyServices as $emergency) {
    $notifications[] = [
        "type" => "emergency",
        "message" => "🚨 Emergency request from {$emergency['full_name']}",
        "link" => "emergency.html?id={$emergency['emergency_id']}"
    ];
}

// Send JSON response
echo json_encode(["success" => true, "notifications" => $notifications]);
?>
