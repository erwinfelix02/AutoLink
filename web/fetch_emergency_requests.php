<?php
require 'config.php'; // Database connection

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT emergency_id, full_name, vehicle, service_needed, latitude, longitude, request_time, status, other_info, price FROM emergency_service ORDER BY request_time DESC");
    $emergencyRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "requests" => $emergencyRequests]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>
