<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Content-Type: application/json");

require 'config.php'; // Ensure this sets up a PDO connection ($pdo)

// Log incoming data for debugging
error_log(print_r($_POST, true)); // Logs text form fields
error_log(print_r($_FILES, true)); // Logs uploaded files

// Check if the request is a POST and has the necessary data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_url']) && isset($_POST['name']) && isset($_POST['price']) && isset($_POST['description'])) {

   // Get form data
   $name = $_POST['name'];
   $price = $_POST['price'];
   $description = $_POST['description'];

   // Handle file upload
   $image = $_FILES['image_url'];
   $uploadDir = 'uploads/';
   $imagePath = $uploadDir . basename($image['name']);
   
   // Validate image upload
   if ($image['error'] !== UPLOAD_ERR_OK) {
       echo json_encode(["success" => false, "message" => "Failed to upload image"]);
       exit();
   }
   
   // Move uploaded image to desired directory
   if (move_uploaded_file($image['tmp_name'], $imagePath)) {
       // Proceed with database insert
       try {
           $stmt = $pdo->prepare("INSERT INTO services (name, price, description, image_url) VALUES (:name, :price, :description, :image_url)");
           $stmt->bindParam(':name', $name);
           $stmt->bindParam(':price', $price);
           $stmt->bindParam(':description', $description);
           $stmt->bindParam(':image_url', $imagePath);  // Store image path in DB

           // Execute query
           if ($stmt->execute()) {
               echo json_encode(["success" => true, "message" => "Service added successfully"]);
           } else {
               echo json_encode(["success" => false, "message" => "Failed to add service"]);
           }
       } catch (PDOException $e) {
           echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
       }
   } else {
       echo json_encode(["success" => false, "message" => "Failed to move uploaded image"]);
   }
} else {
   echo json_encode(["success" => false, "message" => "Invalid input or missing file"]);
}
?>
