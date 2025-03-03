<?php
header("Content-Type: application/json");
require 'config.php'; 

$response = ["success" => false, "message" => "Invalid request."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(empty($_POST)) {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if(is_array($data)) {
            $_POST = $data;
        }
    }
    // Retrieve form data
    $admin_id   = $_POST['admin_id']   ?? null;
    $first_name = $_POST['first_name'] ?? null;
    $last_name  = $_POST['last_name']  ?? null;
    $email      = $_POST['email']      ?? null;
    $address    = $_POST['address']    ?? null;  
    $phone      = $_POST['phone']      ?? null;  
    $profile_image = $_FILES['profile_image'] ?? null;

    // Check required fields (phone can be optional)
    if ($admin_id && $first_name && $last_name && $email) {
        try {
            $sql = "UPDATE admins
                    SET first_name = :first_name,
                        last_name  = :last_name,
                        email      = :email,
                        address    = :address,
                        phone      = :phone
                    WHERE id = :admin_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':address'    => $address,
                ':phone'      => $phone,
                ':admin_id'   => $admin_id
            ]);

            // 2) Handle optional profile_image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "profile_images/";  // Note the folder name now is "profile_images"
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Validate file type (only JPG, PNG)
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = $_FILES['profile_image']['type'];
                if (!in_array($fileType, $allowedTypes)) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Only JPG and PNG files are allowed."
                    ]);
                    exit;
                }

                // Generate unique filename
                $fileName = time() . "_" . basename($_FILES['profile_image']['name']);
                $uploadPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                    // Update DB with the filename (store filename only)
                    $sql = "UPDATE admins
                            SET profile_image = :profile_image
                            WHERE id = :admin_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':profile_image' => $fileName,
                        ':admin_id'      => $admin_id
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to move uploaded file."
                    ]);
                    exit;
                }
            }

            $response = ["success" => true, "message" => "Profile updated successfully!"];
        } catch (PDOException $e) {
            $response = ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    } else {
        $response = ["success" => false, "message" => "Missing required fields."];
    }
} else {
    $response = ["success" => false, "message" => "Invalid request method."];
}

echo json_encode($response);
?>
