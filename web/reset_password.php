<?php
require 'config.php'; 
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? "";
    $new_password = $_POST['new_password'] ?? "";

    if (empty($email) || empty($new_password)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    try {
        // Update the password using the email
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Password has been reset successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to reset password. Email may not exist."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
