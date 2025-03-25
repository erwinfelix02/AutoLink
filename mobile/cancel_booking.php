<?php
header("Content-Type: application/json");
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;

    if ($booking_id) {
        try {
            // First, check if the booking exists in `bookings`
            $query = "SELECT booking_id FROM bookings WHERE booking_id = :booking_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Cancel regular booking
                $updateQuery = "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = :booking_id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
                if ($updateStmt->execute()) {
                    echo json_encode(["success" => true, "message" => "Booking cancelled successfully"]);
                    exit;
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to cancel booking"]);
                    exit;
                }
            }

            // If not found in `bookings`, check in `emergency_service`
            $query = "SELECT emergency_id FROM emergency_service WHERE emergency_id = :booking_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Cancel emergency service request
                $updateQuery = "UPDATE emergency_service SET status = 'Cancelled' WHERE emergency_id = :booking_id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
                if ($updateStmt->execute()) {
                    echo json_encode(["success" => true, "message" => "Emergency service request cancelled successfully"]);
                    exit;
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to cancel emergency service request"]);
                    exit;
                }
            }

            // If the ID is not found in both tables
            echo json_encode(["success" => false, "message" => "Booking ID not found"]);
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
