<?php
require 'config.php'; // Ensure correct config path

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing email parameter"]);
        exit;
    }

    $verification_code = rand(100000, 999999);
    $expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    try {
        $query = "UPDATE users SET reset_code = ?, reset_expiry = ? WHERE LOWER(email) = LOWER(?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $verification_code, $expiry_time, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $subject = "Your Verification Code";
            $message = "Your new verification code is: " . $verification_code . "\nThis code expires in 10 minutes.";
            $headers = "From: noreply@autolink.com";

            if (mail($email, $subject, $message, $headers)) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Verification code resent successfully."]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to send verification email."]);
                exit;
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Email not found or no changes made."]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}
?>
