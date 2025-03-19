<?php
header("Content-Type: application/json");
require 'config.php';

$response = ["success" => false, "message" => "Invalid request."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST)) {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if (is_array($data)) {
            $_POST = $data;
        }
    }

    // Retrieve form data
    $admin_id   = $_POST['adminid']   ?? null; 
    $first_name = $_POST['firstname'] ?? null; 
    $last_name  = $_POST['lastname']  ?? null; 
    $email      = $_POST['email']      ?? null;
    $address    = $_POST['address']    ?? null;
    $phone      = $_POST['phone']      ?? null;
    
    
    
    // Check required fields
    if ($admin_id && $first_name && $last_name && $email) {
        try {
            // ✅ Check if email already exists for another admin
            $checkEmailQuery = "SELECT id FROM admins WHERE email = :email AND id != :admin_id";
            $stmt = $pdo->prepare($checkEmailQuery);
            $stmt->execute(['email' => $email, 'admin_id' => $admin_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Email already exists."]);
                exit;
            }

            // ✅ Update admin profile (excluding profile image first)
            $sql = "UPDATE admins SET first_name = :first_name, last_name = :last_name, email = :email, address = :address, phone = :phone WHERE id = :admin_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':address'    => $address,
                ':phone'      => $phone,
                ':admin_id'   => $admin_id
            ]);

            // ✅ Handle Profile Image Upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/profile_images/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    echo json_encode(["success" => false, "message" => "Failed to create image directory."]);
                    exit;
                }

                // Validate file type (only JPG, PNG)
                $fileMimeType = mime_content_type($_FILES['profile_image']['tmp_name']);
                $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowedTypes = ['image/jpeg', 'image/png'];

                if (!in_array($fileMimeType, $allowedTypes) || !in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                    echo json_encode(["success" => false, "message" => "Invalid file type. Only JPG and PNG allowed."]);
                    exit;
                }

                // Generate unique filename
                $fileName = time() . "_" . $admin_id . "." . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                    $imagePath = "profile_images/" . $fileName; // Store relative path
                    $sql = "UPDATE admins SET profile_image = :profile_image WHERE id = :admin_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':profile_image' => $imagePath, ':admin_id' => $admin_id]);

                    // Update session
                    session_start();
                    $_SESSION['profile_picture'] = $imagePath;
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
                    exit;
                }
            }

            // ✅ Fetch the updated profile image path from the database
            $sql = "SELECT profile_image FROM admins WHERE id = :admin_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':admin_id' => $admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = [
                "success" => true,
                "message" => "Profile updated successfully!",
                "profile_image" => $admin['profile_image'] ?? null // Return image path
            ];
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
