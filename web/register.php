<?php
// api/web/admin/register.php

header('Content-Type: application/json');
require 'config.php'; // Tamang path para sa config file

// Kunin ang input data mula sa request (email at password)
$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Hash ang password bago isave sa database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Maghanda ng SQL query para mag-insert ng bagong admin sa database
        $query = "INSERT INTO admins (email, password) VALUES (:email, :password)";
        $stmt = $db->prepare($query); // Gamitin ang $db para sa database connection
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        
        // Execute ang query
        $stmt->execute();

        // Magbalik ng success message
        echo json_encode([
            'success' => true,
            'message' => 'Admin account created successfully'
        ]);
    } catch (PDOException $e) {
        // Kung may error sa database connection o query execution
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // Kung walang email o password, magbalik ng error message
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
}
?>

