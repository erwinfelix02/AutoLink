<?php
// api/web/admin/login.php

header('Content-Type: application/json');
require 'config.php'; 
require 'function.php';

// Get input data from the request
$data = json_decode(file_get_contents("php://input"));

// Ensure that email and password are provided
if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    try {
        // Prepare SQL query to validate login credentials
        $query = "SELECT * FROM admins WHERE email = :email";
        $stmt = $pdo->prepare($query); // Use $pdo here instead of $conn
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Get the admin data from the database
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // If admin exists and the password matches
        if ($admin && password_verify($password, $admin['password'])) {
            // Create JWT Token for successful login
            $token = generate_jwt($admin['id']);
            
            // Return success message and token
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token
            ]);
        } else {
            // If credentials are incorrect, return error message
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }
    } catch (PDOException $e) {
        // If there is a database connection or query execution error
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // If email or password is missing, return error message
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
}
?>
