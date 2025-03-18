<?php
header("Content-Type: application/json");
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;

    if ($booking_id) {
        try {
            $query = "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = :booking_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Booking cancelled successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to cancel booking"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
