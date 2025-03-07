<?php
header("Content-Type: application/json");
require "config.php";

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["service_id"], $_POST["service_name"], $_POST["service_price"], $_POST["service_description"])) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    $service_id = intval($_POST["service_id"]); // Ensure it's an integer
    $service_name = trim($_POST["service_name"]);
    $service_price = floatval($_POST["service_price"]); // Ensure it's a valid number
    $service_description = trim($_POST["service_description"]);
    $image_path = null;

    try {
        $pdo->beginTransaction();

        // Check if an image was uploaded
        if (isset($_FILES["service_image"]) && $_FILES["service_image"]["error"] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validate file size (max 2MB)
            if ($_FILES["service_image"]["size"] > 2 * 1024 * 1024) {
                throw new Exception("Image file is too large. Max size: 2MB.");
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
                $image_path = $image_name; // Store only the filename
            } else {
                throw new Exception("Failed to upload image.");
            }
        }

        // Update service details
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
    } catch (Exception $e) {
        $pdo->rollBack();
        $response["message"] = "Error: " . $e->getMessage();
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
?>
