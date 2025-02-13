<?php
// api/web/admin/register.php

header('Content-Type: application/json');
require 'config.php'; 

// Get input data from the request (email and password)
$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Hash the password before saving it to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Ensure that the $pdo (database connection) is connected
        if ($pdo) {
            // Prepare SQL query to insert a new admin into the database
            $query = "INSERT INTO admins (email, password) VALUES (:email, :password)";
            $stmt = $pdo->prepare($query); // Use $pdo for the database connection
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            // Execute the query
            $stmt->execute();

            // Return success message
            echo json_encode([
                'success' => true,
                'message' => 'Admin account created successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed.'
            ]);
        }
    } catch (PDOException $e) {
        // If there is an error in database connection or query execution
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
