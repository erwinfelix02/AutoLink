<?php
require 'config.php';
header("Content-Type: application/json");

// Check if required parameters are provided
if (!isset($_GET['user_email']) || !isset($_GET['vehicle_make']) || !isset($_GET['vehicle_model'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$user_email = $_GET['user_email'];
$vehicle_make = $_GET['vehicle_make'];
$vehicle_model = $_GET['vehicle_model'];

$query = "
    SELECT 
        COALESCE(SUM(f.odometer), 0) AS distance,
        COUNT(f.id) AS fillUps,
        COALESCE(SUM(f.quantity), 0) AS fuelQuantity,
        COALESCE(SUM(f.cost), 0) AS fuelCost,

        -- Fetch service cost from completed bookings only
        (
            SELECT COALESCE(SUM(b.service_price), 0) 
            FROM bookings b  
            WHERE b.user_email = :user_email 
            AND b.selected_vehicle = CONCAT(:vehicle_make, ' ', :vehicle_model)
            AND b.status = 'completed'
        ) AS serviceCost,

        -- Calculate total cost (fuel cost + completed service cost)
        (
            COALESCE(SUM(f.cost), 0) + 
            COALESCE(
                (SELECT SUM(b.service_price) 
                 FROM bookings b 
                 WHERE b.user_email = :user_email 
                 AND b.selected_vehicle = CONCAT(:vehicle_make, ' ', :vehicle_model)
                 AND b.status = 'completed'
                ), 0
            )
        ) AS totalCost

    FROM vehicle_fillups f  
    WHERE f.user_email = :user_email
    AND f.vehicle_make = :vehicle_make
    AND f.vehicle_model = :vehicle_model
    GROUP BY f.vehicle_make, f.vehicle_model";

try {
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->bindParam(':vehicle_make', $vehicle_make, PDO::PARAM_STR);
    $stmt->bindParam(':vehicle_model', $vehicle_model, PDO::PARAM_STR);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode(['success' => true, 'data' => [$vehicle]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No vehicle details found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
}
?>
