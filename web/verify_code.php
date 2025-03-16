<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');

    if (empty($email) || empty($code)) {
        echo json_encode(["status" => "error", "message" => "Missing email or code"]);
        exit;
    }

    try {
        // Check if email and code exist in the database
        $stmt = $pdo->prepare("SELECT id, reset_expiry FROM admins WHERE email = ? AND reset_code = ?");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (strtotime($user['reset_expiry']) > time()) {
                echo json_encode(["status" => "success", "message" => "Code verified"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Verification code expired"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid verification code"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
