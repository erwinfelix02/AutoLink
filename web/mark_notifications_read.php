<?php
require 'config.php';
header("Content-Type: application/json");

try {
    $pdo->query("UPDATE appointments SET status = 'Read' WHERE status = 'Pending'");
    $pdo->query("UPDATE feedback SET reply = 'Read' WHERE reply IS NULL");
    $pdo->query("UPDATE emergency_service SET status = 'Read' WHERE status = 'Pending'");

    echo json_encode(["success" => true, "message" => "Notifications marked as read."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
