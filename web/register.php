<?php  
// api/web/admin/register.php

header('Content-Type: application/json');
require 'config.php'; 

// Get input data from the request (first name, last name, email, password)
$data = json_decode(file_get_contents("php://input"));

if (isset($data->first_name) && isset($data->last_name) && isset($data->email) && isset($data->password)) {
    $first_name = $data->first_name;
    $last_name = $data->last_name;
    $email = $data->email;
    $password = $data->password;

    // Hash the password before saving it to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($pdo) {
            $query = "INSERT INTO admins (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
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
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'First name, last name, email, and password are required'
    ]);
}
?>
