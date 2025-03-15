<?php
header("Content-Type: application/json");
require 'config.php';

if (isset($_GET['email'], $_GET['service_type'], $_GET['date_of_service'])) {
    $email = $_GET['email'];
    $serviceType = $_GET['service_type'];
    $dateOfService = $_GET['date_of_service'];

    try {
        $stmt = $conn->prepare("SELECT feedback FROM feedback WHERE email = :email AND service_type = :serviceType AND date_of_service = :dateOfService");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":serviceType", $serviceType, PDO::PARAM_STR);
        $stmt->bindParam(":dateOfService", $dateOfService, PDO::PARAM_STR);
        $stmt->execute();

        $feedback = $stmt->fetchColumn();

        if ($feedback !== false) {
            echo json_encode(["success" => true, "feedback" => $feedback]);
        } else {
            echo json_encode(["success" => false, "feedback" => "No feedback yet"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
