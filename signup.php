<?php
header("Content-Type: application/json");
require 'config.php'; // Include database configuration

$data = json_decode(file_get_contents("php://input"), true); // Parse JSON input

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $fullName = isset($data['fullName']) ? trim($data['fullName']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $password = isset($data['password']) ? trim($data['password']) : null;

    // Validate input data
    if (empty($fullName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if the email is valid and ends with '@gmail.com'
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    if (strpos($email, '@gmail.com') === false) {
        echo json_encode(['success' => false, 'message' => 'Email must be a Gmail address (e.g., example@gmail.com)']);
        exit;
    }

    try {
        // Check if the email already exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email is already registered']);
            exit;
        }

        // Hash the password before storing it in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (:fullName, :email, :password)");
        $stmt->bindParam(':fullName', $fullName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();

        // Return success message
        echo json_encode(['success' => true, 'message' => 'Signup successful']);
    } catch (Exception $e) {
        // Return error message on failure
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    // If the request method is not POST, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
