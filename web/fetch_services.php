<?php
require 'config.php';

header('Content-Type: application/json');

$base_url = "http://localhost/AutoLink/web/uploads/";

try {
    // Fetch necessary fields from the services table
    $stmt = $pdo->query("SELECT id, name, price, description, image_url, created_at FROM services ORDER BY name ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($services as &$service) {
        // Ensure a proper image URL
        $service['image_url'] = !empty($service['image_url']) ? $base_url . basename($service['image_url']) : $base_url . "default.jpg";
    }

    // Return structured JSON response
    echo json_encode([
        'success' => true,
        'services' => $services
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
}
?>
