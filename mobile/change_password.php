<?php
require 'config.php'; // Ensure this path is correct

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

// Debugging: Log received request data
file_put_contents("debug.log", print_r($data, true), FILE_APPEND);

// Validate incoming data
if (!isset($data->id, $data->current_password, $data->new_password)) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit();
}

$user_id = intval($data->id);  // Ensure ID is an integer
$current_password = trim($data->current_password);
$new_password = trim($data->new_password);

try {
    // Check if user exists using ID
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit();
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(["success" => false, "message" => "Incorrect current password"]);
        exit();
    }

    // Check if the new password is the same as the current password
    if (password_verify($new_password, $user['password'])) {
        echo json_encode(["success" => false, "message" => "New password cannot be the same as the current password"]);
        exit();
    }

    // Hash the new password
    $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($updateStmt->execute([$new_password_hashed, $user_id])) {
        echo json_encode(["success" => true, "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
