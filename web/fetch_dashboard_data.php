<?php
require 'config.php'; // Include database connection

header('Content-Type: application/json');

$response = [];

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔹 Total Appointments (Bookings)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM bookings");
    $response['total_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 🔹 Pending Service Requests (Only 'New' Status)
    $stmt = $pdo->query("SELECT COUNT(*) AS pending FROM bookings WHERE status = 'New'");
    $response['pending_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

    // 🔹 Total Vehicles Managed
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM vehicles");
    $response['total_vehicles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 🔹 Total Users
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $response['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
