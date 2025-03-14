<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    if (isset($_FILES['image'])) {
        $image = $_FILES['image'];
        $imageName = time() . "_" . basename($image['name']); // ✅ Unique filename
        $imagePath = '../uploads/' . $imageName; // ✅ Actual file path

        // Move uploaded file
        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            // ✅ Save filename only (not the full path)
            $stmt = $pdo->prepare("INSERT INTO services (name, price, description, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $price, $description, $imageName]);

            echo json_encode(["status" => "success", "message" => "Service added!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No image uploaded."]);
    }
}


?>
