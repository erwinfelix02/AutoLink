<?php
require 'config.php'; // Include database connection

header('Content-Type: application/json');

$response = [];

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔹 Initialize all months with zero
    $months = array_fill(1, 12, 0);

    // 🔹 Fetch actual appointment counts per month
    $stmt = $pdo->query("
        SELECT MONTH(booking_date) AS month, COUNT(*) AS count
        FROM bookings
        GROUP BY MONTH(booking_date)
        ORDER BY month ASC
    ");
    $monthly_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Merge fetched data with initialized months
    foreach ($monthly_appointments as $row) {
        $months[$row['month']] = $row['count'];
    }

    // 🔹 Convert to JSON format
    $formatted_appointments = [];
    foreach ($months as $month => $count) {
        $formatted_appointments[] = ["month" => $month, "count" => $count];
    }

    $response['monthly_appointments'] = $formatted_appointments;

    // 🔹 Service Requests (Count per Service Type)
    $stmt = $pdo->query("
        SELECT service_name, COUNT(*) AS count
        FROM bookings
        GROUP BY service_name
        ORDER BY count DESC
    ");
    $response['service_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Appointment Status Count
    $stmt = $pdo->query("
        SELECT status, COUNT(*) AS count
        FROM bookings
        GROUP BY status
    ");
    $response['appointment_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>
