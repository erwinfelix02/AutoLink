<?php
require 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    if (isset($_FILES['image'])) {
        $image = $_FILES['image'];
        $imageName = time() . "_" . basename($image['name']); // Unique filename
        $imagePath = '../uploads/' . $imageName; // Save inside 'uploads' folder

        // Move uploaded file to the 'uploads' folder
        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            // Save image filename in the database (Not the full path)
            $stmt = $conn->prepare("INSERT INTO services (name, description, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $imageName);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Service added!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database error."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No image uploaded."]);
    }
}
?>
