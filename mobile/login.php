<?php
header("Content-Type: application/json");
require 'config.php'; // Include database configuration file

// Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if JSON body is valid and contains necessary fields
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    try {
        // Check if the user exists in the database
        $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Fetch user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Respond with success and user data
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'userId' => $user['id'],
                    'fullName' => $user['full_name'],
                    'email' => $user['email']
                ]);
            } else {
                // Invalid password
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            // User not found
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } catch (Exception $e) {
        // Handle any other errors (generic error message)
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
        error_log($e->getMessage());
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
