<?php
require 'config.php'; // Database connection

header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch customers
    $stmt = $pdo->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch services for each customer
    foreach ($customers as &$customer) {
        $stmt = $pdo->prepare("SELECT service_name, booking_date, service_price, service_description, status, booking_time 
                               FROM bookings WHERE user_id = ?");
        $stmt->execute([$customer['id']]);
        $customer['services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($customers);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
