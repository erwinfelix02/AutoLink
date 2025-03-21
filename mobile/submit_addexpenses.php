<?php
header("Content-Type: application/json");
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get the POST data
        $user_email = $_POST['user_email'];
        $full_name = $_POST['full_name'];
        $vehicle_make = $_POST['vehicle_make'];
        $vehicle_model = $_POST['vehicle_model'];
        $odometer = $_POST['odometer'];
        $expenses = $_POST['expenses'];
        $cost = $_POST['cost'];
        $vendor = $_POST['vendor'];
        $fill_date = $_POST['fill_date'];

        // Validate required fields
        if (!$user_email || !$full_name || !$vehicle_make || !$vehicle_model || !$odometer || !$expenses || !$cost || !$vendor || !$fill_date) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit;
        }

        // SQL query to insert data
        $sql = "INSERT INTO vehicle_expenses (user_email, full_name, vehicle_make, vehicle_model, odometer, expenses, cost, vendor, fill_date) 
                VALUES (:user_email, :full_name, :vehicle_make, :vehicle_model, :odometer, :expenses, :cost, :vendor, :fill_date)";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_email', $user_email);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':vehicle_make', $vehicle_make);
        $stmt->bindParam(':vehicle_model', $vehicle_model);
        $stmt->bindParam(':odometer', $odometer);
        $stmt->bindParam(':expenses', $expenses);
        $stmt->bindParam(':cost', $cost);
        $stmt->bindParam(':vendor', $vendor);
        $stmt->bindParam(':fill_date', $fill_date);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Expense added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add expense']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    // Close the connection
    $conn = null;
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
