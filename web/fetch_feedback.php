<?php
require 'config.php'; // Database connection

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, customer_name, email, service_type, feedback, reply FROM feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($feedbacks);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
