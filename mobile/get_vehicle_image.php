<?php
require 'config.php'; // Include the database connection

header("Content-Type: application/json");

// Get email parameter from query
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

try {
    // Query to get all vehicle images for the given email
    $query = "SELECT image_path FROM vehicles WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        $imageUrls = [];

    
        foreach ($results as $row) {
            if (!empty($row['image_path'])) {
                $imageUrls[] = "http://localhost/AutoLink/mobile/" . $row['image_path'];
            }
        }

        if (!empty($imageUrls)) {
            echo json_encode([
                "success" => true,
                "imageUrls" => $imageUrls
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No images found"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No vehicle images found for this email"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

// Close the connection
$stmt = null;
$conn = null;
?>
