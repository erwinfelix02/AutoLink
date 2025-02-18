<?php
require 'config.php';

header('Content-Type: application/json');

$base_url = "http://localhost/AutoLink/web/uploads/"; 

try {
    $stmt = $pdo->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($services as &$service) {
        if (!empty($service['image_url'])) {
            if (str_starts_with($service['image_url'], "uploads/")) {
                $service['image_url'] = $base_url . basename($service['image_url']);
            }
        } else {
            $service['image_url'] = $base_url . "default.jpg"; 
        }
    }

    echo json_encode($services);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching services: ' . $e->getMessage()]);
}
?>
