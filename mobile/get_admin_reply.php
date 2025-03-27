<?php
header("Content-Type: application/json");
require 'config.php';

if (isset($_GET['email'], $_GET['serviceType'], $_GET['dateOfService'])) {
    $email = $_GET['email'];
    $serviceType = $_GET['serviceType'];
    $dateOfService = $_GET['dateOfService'];

    try {
        $stmt = $conn->prepare("SELECT reply FROM feedback WHERE email = :email AND service_type = :serviceType AND date_of_service = :dateOfService");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':serviceType', $serviceType);
        $stmt->bindParam(':dateOfService', $dateOfService);
        $stmt->execute();
        
        $reply = $stmt->fetchColumn();
        echo json_encode([
            "success" => true,
            "reply" => $reply !== false && !empty($reply) ? $reply : "No reply yet"
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "reply" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "reply" => "Invalid request"]);
}
?>
