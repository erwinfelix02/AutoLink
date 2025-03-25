<?php 
require 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$code = trim($_POST['code'] ?? '');

if (empty($email) || empty($code)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing email or code"]);
    exit;
}

try {
    $query = "SELECT id, email, reset_expiry FROM users WHERE LOWER(email) = LOWER(:email) AND reset_code = :code";
    $stmt = $conn->prepare($query);
    $stmt->execute(['email' => $email, 'code' => $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (strtotime($user['reset_expiry']) > time()) {
            echo json_encode(["status" => "success", "message" => "Code verified", "email" => $user['email']]);
        } else {
            echo json_encode(["status" => "error", "message" => "Verification code expired"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid verification code"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}

?>
