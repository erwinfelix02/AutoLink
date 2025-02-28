<?php
require 'config.php';  // Ensure config.php correctly initializes $conn

if (!isset($_GET['email']) || !isset($_GET['new_password'])) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$email = trim($_GET['email']);
$new_password = password_hash(trim($_GET['new_password']), PASSWORD_DEFAULT);

try {
    // Ensure $conn is defined
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

    // Use a prepared statement with PDO
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->bindParam(':password', $new_password, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
