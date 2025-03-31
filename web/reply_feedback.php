<?php
require 'config.php'; // Database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $feedback_id = $data["id"];
    $reply = $data["reply"];

    try {
        $stmt = $pdo->prepare("UPDATE feedback SET reply = ? WHERE id = ?");
        $stmt->execute([$reply, $feedback_id]);

        echo json_encode(["success" => true, "message" => "Reply sent successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
