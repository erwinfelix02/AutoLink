<?php
header('Content-Type: application/json');
require 'config.php';  // This file should initialize $pdo

$response = ["success" => false, "message" => "Invalid request."];

// Get admin_id from GET, POST, or JSON body.
$admin_id = $_GET['admin_id'] ?? $_POST['admin_id'] ?? null;
if (!$admin_id) {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    $admin_id = $data['admin_id'] ?? null;
}

if ($admin_id) {
    $sql = "SELECT id, first_name, last_name, email, address, phone, profile_image, created_at 
            FROM admins 
            WHERE id = :admin_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':admin_id' => $admin_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
        // Build full URL for the stored filename from folder "profile_images"
        if (!empty($account['profile_image'])) {
            // Assume stored value is just the filename
            $profileImageURL = "http://localhost/Autolink/web/profile_images/" . $account['profile_image'];
        } else {
            $profileImageURL = "http://localhost/Autolink/web/profile_images/default.png";
        }
        
        $response = [
            "success" => true,
            "account" => [
                "id"            => $account['id'],
                "first_name"    => $account['first_name'],
                "last_name"     => $account['last_name'],
                "email"         => $account['email'],
                "address"       => $account['address'],
                "phone"         => $account['phone'],
                "profile_image" => $profileImageURL,
                "created_at"    => $account['created_at']
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
