<?php
header("Content-Type: application/json");
require 'config.php'; // Include the PDO connection

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'] ?? null;
    $full_name = $_POST['full_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $profile_image = $_FILES['profile_image'] ?? null;

    if ($admin_id && $full_name && $email) {
        try {
            // Update name and email in the database
            $query = "UPDATE admins SET full_name = :full_name, email = :email WHERE id = :admin_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':admin_id' => $admin_id
            ]);

            // Handle profile image upload if provided and without errors
            if (!empty($profile_image) && $profile_image['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "profile_pictures/"; // Folder where images will be stored

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Validate file type (Only allow JPG and PNG)
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = $profile_image['type'];
                if (!in_array($fileType, $allowedTypes)) {
                    echo json_encode(["status" => "error", "message" => "Only JPG and PNG files are allowed."]);
                    exit();
                }

                // Generate a unique filename and build the path
                $image_path = $uploadDir . time() . "_" . basename($profile_image['name']);
                if (move_uploaded_file($profile_image['tmp_name'], $image_path)) {
                    // Update the profile_image column in the database with the new file path
                    $query = "UPDATE admins SET profile_image = :profile_image WHERE id = :admin_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        ':profile_image' => $image_path,
                        ':admin_id' => $admin_id
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
                    exit();
                }
            }

            $response['status'] = 'success';
            $response['message'] = 'Profile updated successfully!';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
