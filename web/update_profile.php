<?php 
header("Content-Type: application/json");
require 'config.php';
session_start();

$response = ["success" => false, "message" => "Invalid request."];

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST)) {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if (is_array($data)) {
            $_POST = $data;
        }
    }

    $first_name = $_POST['firstname'] ?? null; 
    $last_name  = $_POST['lastname']  ?? null; 
    $email      = $_POST['email']     ?? null;
    $address    = $_POST['address']   ?? null;
    $phone      = $_POST['phone']     ?? null;

    if (!$first_name || !$last_name || !$email) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email AND id != :admin_id");
        $stmt->execute(['email' => $email, 'admin_id' => $admin_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => false, "message" => "Email already exists."]);
            exit;
        }

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

        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/profile_images/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                echo json_encode(["success" => false, "message" => "Failed to create image directory."]);
                exit;
            }

            $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $fileName = time() . "_" . $admin_id . "." . $fileExtension;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $imagePath = "profile_images/" . $fileName;
                $stmt = $pdo->prepare("UPDATE admins SET profile_image = :profile_image WHERE id = :admin_id");
                $stmt->execute([':profile_image' => $imagePath, ':admin_id' => $admin_id]);
                $_SESSION['profile_picture'] = $imagePath;
                $profile_image = $imagePath;
            }
        }

        $stmt = $pdo->prepare("SELECT profile_image FROM admins WHERE id = :admin_id");
        $stmt->execute([':admin_id' => $admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            "success" => true,
            "message" => "Profile updated successfully!",
            "profile_image" => $admin['profile_image'] ?? $profile_image
        ];
    } catch (PDOException $e) {
        $response = ["success" => false, "message" => "Database error: " . $e->getMessage()];
    }
}

echo json_encode($response);
?>
