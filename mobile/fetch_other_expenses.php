<?php
require 'config.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['user_email'] ?? '';
    $vehicle_make = $_POST['vehicle_make'] ?? '';
    $vehicle_model = $_POST['vehicle_model'] ?? '';

    if (!empty($user_email) && !empty($vehicle_make) && !empty($vehicle_model)) {
        try {
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(cost), 0) AS total_cost 
                FROM vehicle_expenses 
                WHERE user_email = :user_email 
                AND vehicle_make = :vehicle_make 
                AND vehicle_model = :vehicle_model
            ");
            $stmt->bindParam(':user_email', $user_email);
            $stmt->bindParam(':vehicle_make', $vehicle_make);
            $stmt->bindParam(':vehicle_model', $vehicle_model);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => ["totalCost" => $result['total_cost']]
            ]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid parameters"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
