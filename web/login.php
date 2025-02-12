<?php
// api/web/admin/login.php

header('Content-Type: application/json');
require 'config.php'; // Tamang path para sa config file
require 'functions.php'; // Include functions file for JWT functions

// Kunin ang input data mula sa request (email at password)
$data = json_decode(file_get_contents("php://input"));

// Tiyakin kung may email at password na ibinigay
if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    try {
        // Maghanda ng SQL query para i-validate ang login credentials
        $query = "SELECT * FROM admins WHERE email = :email";
        $stmt = $db->prepare($query); // Gamitin ang $db para sa database connection
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Kunin ang admin data mula sa database
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kung may admin at tugma ang password
        if ($admin && password_verify($password, $admin['password'])) {
            // Gumawa ng JWT Token para sa matagumpay na login
            $token = generate_jwt($admin['id']); // Gamitin ang function na generate_jwt()

            // Magbalik ng success message at token
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token
            ]);
        } else {
            // Kung maling credentials, magbalik ng error message
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }
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
