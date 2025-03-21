<?php
require 'config.php';
header("Content-Type: application/json");

if (isset($_GET['user_email'], $_GET['vehicle_make'], $_GET['vehicle_model'])) {
    $user_email = $_GET['user_email'];
    $vehicle_make = $_GET['vehicle_make'];
    $vehicle_model = $_GET['vehicle_model'];

    try {
        // Prepare the SQL query with wildcard match
        $query = "SELECT SUM(service_price) AS total_cost FROM bookings 
                  WHERE user_email = :user_email 
                  AND selected_vehicle LIKE CONCAT('%', :vehicle, '%')
                  AND status = 'completed'";
        
        $stmt = $conn->prepare($query);
        $vehicle = "$vehicle_make $vehicle_model";
        
        // Bind parameters
        $stmt->bindValue(":user_email", $user_email, PDO::PARAM_STR);
        $stmt->bindValue(":vehicle", $vehicle, PDO::PARAM_STR);
        
        // Execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return JSON response
        echo json_encode([
            "success" => true,
            "serviceCost" => isset($row['total_cost']) ? floatval($row['total_cost']) : 0.0
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
}
?>
