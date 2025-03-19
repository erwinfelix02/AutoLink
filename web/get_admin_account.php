<?php
session_start();
header('Content-Type: application/json');
require 'config.php'; 

$response = ["success" => false, "message" => "Invalid request."];

// ✅ Get admin_id from session
$admin_id = $_SESSION['admin_id'] ?? null;

if ($admin_id) {
    $sql = "SELECT id, first_name, last_name, email, address, phone, profile_image, created_at 
            FROM admins WHERE id = :admin_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':admin_id' => $admin_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
        // ✅ Ensure correct profile image path
        $baseUrl = "http://localhost/Autolink/web/profile_images/";
        $profileImage = $account['profile_image'] ?? 'default.png';

        // Fix double 'profile_images/' issue
        $profileImage = str_replace('profile_images/', '', $profileImage);
        $profileImage = $baseUrl . $profileImage;

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
        $response = ["success" => false, "message" => "Account not found."];
    }
} else {
    // ✅ Debugging session issue
    $response = [
        "success" => false,
        "message" => "No admin logged in.",
        "debug" => [
            "session_data" => $_SESSION,
            "session_id" => session_id()
        ]
    ];
}

echo json_encode($response);

?>
