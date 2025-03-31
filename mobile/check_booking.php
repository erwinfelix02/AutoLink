<?php
require 'config.php'; // Ensure this path is correct

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $email = $_GET['email'] ?? '';
    $date = $_GET['date'] ?? '';
    $time = $_GET['time'] ?? '';

    if (empty($email) || empty($date) || empty($time)) {
        echo json_encode(["success" => false, "message" => "Missing parameters"]);
        exit();
    }

    try {
        // Check if there is an existing booking with the same date and time that is NOT cancelled
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND booking_time = ? AND status <> 'Cancelled'");
        $stmt->execute([$date, $time]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(["success" => false, "exists" => true, "message" => "This time slot is already booked. Please select another time."]);
        } else {
            echo json_encode(["success" => true, "exists" => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
