<?php
header("Content-Type: application/json");
require 'config.php';

// Get the POST data
$user_email = $_POST['user_email'];
$full_name = $_POST['full_name'];
$vehicle_make = $_POST['vehicle_make'];
$vehicle_model = $_POST['vehicle_model'];
$odometer = $_POST['odometer'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$cost = $_POST['cost'];
$filling_station = $_POST['filling_station'];
$fill_date = $_POST['fill_date'];

try {
    // SQL query to insert data
    $sql = "INSERT INTO vehicle_fillups (user_email, full_name, vehicle_make, vehicle_model, odometer, quantity, price, cost, filling_station, fill_date) 
            VALUES (:user_email, :full_name, :vehicle_make, :vehicle_model, :odometer, :quantity, :price, :cost, :filling_station, :fill_date)";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':user_email', $user_email);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':vehicle_make', $vehicle_make);
    $stmt->bindParam(':vehicle_model', $vehicle_model);
    $stmt->bindParam(':odometer', $odometer);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':cost', $cost);
    $stmt->bindParam(':filling_station', $filling_station);
    $stmt->bindParam(':fill_date', $fill_date);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record inserted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error inserting record']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
