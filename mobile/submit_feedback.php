<?php
header("Content-Type: application/json");
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $date_of_service = $_POST['date_of_service'] ?? '';
    $price = $_POST['price'] ?? '';
    $feedback = $_POST['feedback'] ?? '';

    if (empty($email) || empty($customer_name) || empty($service_type) || empty($date_of_service) || empty($price) || empty($feedback)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO feedback (email, customer_name, service_type, date_of_service, price, feedback) 
                                VALUES (:email, :customer_name, :service_type, :date_of_service, :price, :feedback)");
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':customer_name', $customer_name);
        $stmt->bindParam(':service_type', $service_type);
        $stmt->bindParam(':date_of_service', $date_of_service);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':feedback', $feedback);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Feedback submitted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to submit feedback"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
