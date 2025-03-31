<?php 
session_start();
header('Content-Type: application/json');
require 'config.php'; 

$response = ["success" => false, "message" => "Invalid request."];

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "No admin logged in."]);
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? $_POST['adminid'] ?? null;

try {
    $sql = "SELECT id, first_name, last_name, email, address, phone, profile_image, created_at 
            FROM admins WHERE id = :admin_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':admin_id' => $admin_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
        $baseUrl = "http://localhost/Autolink/web/";

$profileImage = !empty($account['profile_image']) ? $account['profile_image'] : 'profile_images/default.png';

if (!str_contains($profileImage, $baseUrl)) {
    $profileImage = $baseUrl . $profileImage;
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
                "profile_image" => $profileImage,
                "created_at"    => $account['created_at']
            ]
        ];
    } else {
        $response = ["success" => false, "message" => "Admin account not found."];
    }
} catch (PDOException $e) {
    $response = ["success" => false, "message" => "Database error: " . $e->getMessage()];
}

echo json_encode($response);
?>
