<?php
require 'config.php';  // Ensure config.php correctly initializes $conn

// Check if 'email' is set in the GET request
if (!isset($_GET['email'])) {
    echo json_encode(["success" => false, "message" => "Email parameter is missing."]);
    exit;
}

$email = trim($_GET['email']); // Sanitize input

try {
    // Ensure $conn is defined
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the result
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(["success" => true, "message" => "Email exists! Proceed with password reset."]);
    } else {
        echo json_encode(["success" => false, "message" => "Email not found."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
