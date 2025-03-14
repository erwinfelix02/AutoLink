<?php
header("Content-Type: application/json");
require "config.php";

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang mga datos mula sa POST request
    $service_id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
    $service_name = isset($_POST["name"]) ? trim($_POST["name"]) : null;
    $service_price = isset($_POST["price"]) ? floatval($_POST["price"]) : null;
    $service_description = isset($_POST["description"]) ? trim($_POST["description"]) : null;
    $image_path = null;

    // Debugging logs
    error_log("Received ID: " . $service_id);
    error_log("Received Name: " . $service_name);
    error_log("Received Price: " . $service_price);
    error_log("Received Description: " . $service_description);

    if (!$service_id || !$service_name || !$service_price || !$service_description) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Kunin ang kasalukuyang image path mula sa database
        $stmt = $pdo->prepare("SELECT image_url FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $currentImage = $stmt->fetchColumn(); // Fetch current image

        // Debugging log
        error_log("Current Image Path: " . $currentImage);

        // Check kung may bagong image na na-upload
        if (isset($_FILES["service_image"]) && $_FILES["service_image"]["error"] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validate file type
            $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
            if (!in_array($_FILES["service_image"]["type"], $allowed_types)) {
                throw new Exception("Invalid image format. Only JPG and PNG are allowed.");
            }

            // Generate unique file name
            $image_ext = pathinfo($_FILES["service_image"]["name"], PATHINFO_EXTENSION);
            $image_name = uniqid("service_", true) . "." . $image_ext;
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/" . $image_name; // Ensure it includes "uploads/"

                // Optional: Delete old image if exists
                if (!empty($currentImage) && file_exists($currentImage)) {
                    unlink($currentImage);
                }

                // Debugging log
                error_log("New Image Path: " . $image_path);
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            $image_path = $currentImage; // Keep old image if no new one uploaded
        }

        // Update service details with or without image update
        if ($image_path) {
            $stmt = $pdo->prepare("UPDATE services SET name=?, price=?, description=?, image_url=? WHERE id=?");
            $stmt->execute([$service_name, $service_price, $service_description, $image_path, $service_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE services SET name=?, price=?, description=? WHERE id=?");
            $stmt->execute([$service_name, $service_price, $service_description, $service_id]);
        }

        $pdo->commit();
        $response["success"] = true;
        $response["message"] = "Service updated successfully.";
        $response["image_url"] = $image_path; // Send back the saved image path for confirmation
    } catch (Exception $e) {
        $pdo->rollBack();
        $response["message"] = "Error: " . $e->getMessage();
        error_log("Update Error: " . $e->getMessage());
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
?>
