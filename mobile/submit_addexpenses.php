<?php
header("Content-Type: application/json");
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $user_email = filter_input(INPUT_POST, 'user_email', FILTER_SANITIZE_EMAIL);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $vehicle_make = filter_input(INPUT_POST, 'vehicle_make', FILTER_SANITIZE_STRING);
    $vehicle_model = filter_input(INPUT_POST, 'vehicle_model', FILTER_SANITIZE_STRING);
    $odometer = filter_input(INPUT_POST, 'odometer', FILTER_VALIDATE_INT);
    $expenses = filter_input(INPUT_POST, 'expenses', FILTER_VALIDATE_FLOAT);
    $cost = filter_input(INPUT_POST, 'cost', FILTER_VALIDATE_FLOAT);
    $vendor = filter_input(INPUT_POST, 'vendor', FILTER_SANITIZE_STRING);
    $fill_date = filter_input(INPUT_POST, 'fill_date', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$user_email || !$full_name || !$vehicle_make || !$vehicle_model || !$odometer ||
        !$expenses || !$cost || !$vendor || !$fill_date) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "All fields are required and must be valid"]);
        exit;
    }

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO vehicle_expenses 
            (user_email, full_name, vehicle_make, vehicle_model, odometer, expenses, cost, vendor, fill_date) 
            VALUES (:user_email, :full_name, :vehicle_make, :vehicle_model, :odometer, :expenses, :cost, :vendor, :fill_date)");

        // Bind parameters
        $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':vehicle_make', $vehicle_make, PDO::PARAM_STR);
        $stmt->bindParam(':vehicle_model', $vehicle_model, PDO::PARAM_STR);
        $stmt->bindParam(':odometer', $odometer, PDO::PARAM_INT);
        $stmt->bindParam(':expenses', $expenses, PDO::PARAM_STR);
        $stmt->bindParam(':cost', $cost, PDO::PARAM_STR);
        $stmt->bindParam(':vendor', $vendor, PDO::PARAM_STR);
        $stmt->bindParam(':fill_date', $fill_date, PDO::PARAM_STR);

        // Execute query
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Expense added successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to add expense"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
