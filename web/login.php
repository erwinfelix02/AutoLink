<?php
session_start();
require 'config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    try {
        $query = "SELECT * FROM admins WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['profile_picture'] = $admin['profile_image']; // ✅ Profile Picture

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'session_data' => $_SESSION,  // ✅ Debug Session Storage
                'session_id' => session_id(), // ✅ Display Session ID
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
}
?>
