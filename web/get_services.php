<?php
require 'config.php'; // Ensure correct path

header('Content-Type: application/json');

$base_url = "http://localhost/AutoLink/web/uploads/"; // Adjust to your actual path

try {
    $stmt = $pdo->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($services as &$service) {
        if (!empty($service['image_url'])) {
            if (str_starts_with($service['image_url'], "uploads/")) {
                // Append full URL if it's a file path
                $service['image_url'] = $base_url . basename($service['image_url']);
            }
            // If it's base64, leave it as is (or consider removing it)
        } else {
            $service['image_url'] = $base_url . "default.jpg"; // Default image if missing
        }
    }

    echo json_encode($services);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching services: ' . $e->getMessage()]);
}
?>
