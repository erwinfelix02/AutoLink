<?php
session_start();
require 'config.php';
header("Content-Type: application/json");

// Debug: Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        "success" => false,
        "error" => "Admin not logged in",
        "session_id" => session_id(),
        "session_data" => $_SESSION
    ]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

try {
    // Fetch admin data
    $sql = "SELECT first_name, last_name, email, profile_picture FROM admins WHERE id = :admin_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Convert profile picture to base64 if it exists
        if (!empty($data['profile_picture'])) {
            $data['profile_picture'] = "data:image/png;base64," . base64_encode($data['profile_picture']);
        } else {
            $data['profile_picture'] = "images/profile.png"; // Default image
        }

        echo json_encode(["success" => true, "data" => $data]);
    } else {
        echo json_encode(["success" => false, "error" => "Admin not found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
