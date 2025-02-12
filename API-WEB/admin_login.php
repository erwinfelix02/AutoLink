<?php
// admin_login.php
header('Content-Type: application/json');
require '../config.php';  

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);

file_put_contents('php://stdout', "Incoming data: " . print_r($data, true) . "\n", FILE_APPEND);  

if (isset($data['email'], $data['password'])) {
    $email = $data['email'];
    $password = $data['password'];  

    $query = "SELECT * FROM users WHERE email = :email AND role = 'admin' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    file_put_contents('php://stdout', "Rows found: " . $stmt->rowCount() . "\n", FILE_APPEND);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        file_put_contents('php://stdout', "Password check: " . $password . " vs " . $user['password'] . "\n", FILE_APPEND);

        if ($password === $user['password']) {
            file_put_contents('php://stdout', "Login successful for user: " . $user['email'] . "\n", FILE_APPEND);

            echo json_encode([
                'success' => true,
                'message' => 'Admin login successful',
                'user' => [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            file_put_contents('php://stdout', "Invalid password for user: " . $user['email'] . "\n", FILE_APPEND);

            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        file_put_contents('php://stdout', "Admin not found for email: " . $email . "\n", FILE_APPEND);

        echo json_encode(['success' => false, 'message' => 'Admin not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
}
?>
