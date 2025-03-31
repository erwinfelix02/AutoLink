<?php
require "config.php";

header("Content-Type: application/json");

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "No booking ID provided"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            "success" => true,
            "name" => $row['user_name'],
            "email" => $row['user_email'],
            "service" => $row['service_name'],
            "price" => $row['service_price'],
            "description" => $row['service_description'],
            "status" => $row['status'],
            "date" => $row['booking_date'],
            "time" => $row['booking_time'],
            "inclusions" => $row['inclusions'],
            "selected_vehicle" => $row['selected_vehicle'], // ✅ Include vehicle
            "created_at" => $row['created_at']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No booking found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

?>
