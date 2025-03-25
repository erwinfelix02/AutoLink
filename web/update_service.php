<?php
header("Content-Type: application/json");
require "config.php";

$response = ["success" => false, "message" => ""];
$base_url = "http://localhost/AutoLink/web/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Check received POST data
    error_log(print_r($_POST, true));

    $service_id = isset($_POST["service_id"]) ? intval($_POST["service_id"]) : null;
    $service_name = isset($_POST["service_name"]) ? trim($_POST["service_name"]) : null;
    $service_price = isset($_POST["service_price"]) ? floatval($_POST["service_price"]) : null;
    $service_description = isset($_POST["service_description"]) ? trim($_POST["service_description"]) : null;
    $image_path = null;

    if (!$service_id || !$service_name || !$service_price || !$service_description) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Kunin ang kasalukuyang image filename mula sa database
        $stmt = $pdo->prepare("SELECT image_url FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $currentImage = $stmt->fetchColumn();

        // Check kung may bagong image na na-upload
        if (isset($_FILES["service_image"]) && $_FILES["service_image"]["error"] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
            if (!in_array($_FILES["service_image"]["type"], $allowed_types)) {
                throw new Exception("Invalid image format. Only JPG and PNG are allowed.");
            }

            // Generate unique filename
            $image_ext = pathinfo($_FILES["service_image"]["name"], PATHINFO_EXTENSION);
            $image_name = uniqid("service_", true) . "." . $image_ext;
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                $image_path = $image_name; // ✅ Store only filename in DB

                // Optional: Delete old image if exists
                if (!empty($currentImage) && file_exists("uploads/" . $currentImage)) {
                    unlink("uploads/" . $currentImage);
                }
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            $image_path = $currentImage; // Keep old image if no new one uploaded
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
        $response["image_url"] = $base_url . "uploads/" . $image_path; // Return full image URL
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
