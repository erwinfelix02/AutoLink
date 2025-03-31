<?php
require 'config.php'; // Database connection

// Fetch services
$stmt = $conn->prepare("SELECT id, name, price, description, image_url FROM services");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Base URL for Emulator
$baseUrl = "http://localhost/AutoLink/web/web/uploads/";

foreach ($services as &$service) {
    // Ensure image URL exists and remove extra spaces
    if (!empty($service['image_url'])) {
        $service['image_url'] = $baseUrl . trim(basename($service['image_url']));
    } else {
        $service['image_url'] = ""; // Return empty string if no image
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'services' => $services]);
?>
