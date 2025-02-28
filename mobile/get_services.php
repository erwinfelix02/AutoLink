<?php
require 'config.php'; // Database connection

// Fetch services
$stmt = $conn->prepare("SELECT id, name, price, description, image_url FROM services");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Base URL for images
$baseUrl = "http://localhost/AutoLink/web/uploads/";

foreach ($services as &$service) {
    // Ensure the image URL is properly formatted
    if (!empty($service['image_url'])) {
        $service['image_url'] = $baseUrl . basename($service['image_url']);
    }
}

// Return JSON response
echo json_encode(['success' => true, 'services' => $services]);
?>
