<?php
include 'db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (isset($data->booking_id) && isset($data->status)) {
    $booking_id = $conn->real_escape_string($data->booking_id);
    $status = $conn->real_escape_string($data->status);

    $sql = "UPDATE bookings SET status='$status' WHERE id='$booking_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Booking status updated"]);
    } else {
        echo json_encode(["error" => "Failed to update booking status"]);
    }
} else {
    echo json_encode(["error" => "Invalid input"]);
}
?>
