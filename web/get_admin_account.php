<?php
header('Content-Type: application/json');
require 'config.php';  

$response = ["success" => false, "message" => "Invalid request."];

// Check if admin_id is sent via GET, POST, or JSON
$admin_id = $_GET['admin_id'] ?? $_POST['admin_id'] ?? null;

// Check if JSON body is used
if (!$admin_id) {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    $admin_id = $data['admin_id'] ?? null;
}

if ($admin_id) {
    $query = "SELECT id, first_name, last_name, email, profile_image, created_at FROM admins WHERE id = :admin_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':admin_id' => $admin_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
        $response = [
            "success" => true,
            "account" => [
                "id" => $account['id'],
                "first_name" => $account['first_name'],
                "last_name" => $account['last_name'],
                "email" => $account['email'],
                "profile_image" => $account['profile_image'] ? $account['profile_image'] : "http://localhost/Autolink/web/profile_pictures/default.png",
                "created_at" => $account['created_at']
            ]
        ];
    } else {
        $response = ["success" => false, "message" => "Account not found."];
    }
} else {
    $response = ["success" => false, "message" => "Missing required parameter: admin_id."];
}

echo json_encode($response);
?>
