<?php 
// api/web/admin/register.php

header('Content-Type: application/json');
require 'config.php'; 

// Get input data from the request (first name, last name, email, password, profile picture)
$data = json_decode(file_get_contents("php://input"));

if (isset($data->first_name) && isset($data->last_name) && isset($data->email) && isset($data->password) && isset($data->profile_picture)) {
    $first_name = $data->first_name;
    $last_name = $data->last_name;
    $email = $data->email;
    $password = $data->password;
    $profile_picture = $data->profile_picture;

    // Hash the password before saving it to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Ensure that the $pdo (database connection) is connected
        if ($pdo) {
            // Prepare SQL query to insert a new admin into the database
            $query = "INSERT INTO admins (first_name, last_name, email, password, profile_picture) VALUES (:first_name, :last_name, :email, :password, :profile_picture)";
            $stmt = $pdo->prepare($query); // Use $pdo for the database connection
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':profile_picture', $profile_picture);

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
    // If required fields are missing, return error message
    echo json_encode([
        'success' => false,
        'message' => 'First name, last name, email, password, and profile picture are required'
    ]);
}
?>