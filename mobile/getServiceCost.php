<?php
require 'config.php';
header("Content-Type: application/json");

if (isset($_GET['user_email'], $_GET['vehicle_make'], $_GET['vehicle_model'])) {
    $user_email = $_GET['user_email'];
    $vehicle_make = $_GET['vehicle_make'];
    $vehicle_model = $_GET['vehicle_model'];
    
    try {
        $vehicle = "$vehicle_make $vehicle_model";

        // Fetch service cost from bookings
        $query1 = "SELECT COALESCE(SUM(service_price), 0) AS total_service_cost 
                   FROM bookings 
                   WHERE user_email = :user_email 
                   AND selected_vehicle LIKE CONCAT('%', :vehicle, '%') 
                   AND LOWER(status) = 'completed'";

        $stmt1 = $conn->prepare($query1);
        $stmt1->bindParam(":user_email", $user_email, PDO::PARAM_STR);
        $stmt1->bindParam(":vehicle", $vehicle, PDO::PARAM_STR);
        $stmt1->execute();
        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        $totalServiceCost = floatval($row1['total_service_cost']);

        // Fetch emergency service cost
        $query2 = "SELECT COALESCE(SUM(price), 0) AS total_emergency_cost 
                   FROM emergency_service 
                   WHERE user_email = :user_email 
                   AND vehicle LIKE CONCAT('%', :vehicle, '%') 
                   AND LOWER(status) = 'completed'";

        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(":user_email", $user_email, PDO::PARAM_STR);
        $stmt2->bindParam(":vehicle", $vehicle, PDO::PARAM_STR);
        $stmt2->execute();
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $totalEmergencyCost = floatval($row2['total_emergency_cost']);

        // Calculate total cost
        $grandTotal = $totalServiceCost + $totalEmergencyCost;

        // Return JSON response
        echo json_encode([
            "success" => true,
            "serviceCost" => $totalServiceCost,
            "emergencyCost" => $totalEmergencyCost,
            "totalCost" => $grandTotal
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
