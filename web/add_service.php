<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

$response = ["success" => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response["message"] = "Invalid request method";
    echo json_encode($response);
    exit();
}

if (!isset($_POST['name'], $_POST['price'], $_POST['description'], $_FILES['image_url'])) {
    $response["message"] = "Missing required parameters";
    echo json_encode($response);
    exit();
}

try {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image = $_FILES['image_url'];
    $imagePath = $uploadDir . basename($image['name']);

    if ($image['error'] !== UPLOAD_ERR_OK) {
        $response["message"] = "File upload error: " . $image['error'];
        echo json_encode($response);
        exit();
    }

    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
        $response["message"] = "Failed to move uploaded file";
        echo json_encode($response);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO services (name, price, description, image_url) VALUES (:name, :price, :description, :image_url)");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':price' => $_POST['price'],
        ':description' => $_POST['description'],
        ':image_url' => $imagePath
    ]);

    $response["success"] = true;
    $response["message"] = "Service added successfully";

} catch (PDOException $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);    
?>
