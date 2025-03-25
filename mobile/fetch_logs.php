<?php
require 'config.php'; // Include the database connection

header("Content-Type: application/json");

// Get the email parameter from the query string and sanitize it to prevent SQL injection
$email = filter_var($_GET['user_email'], FILTER_SANITIZE_EMAIL);

// Check if the email parameter is provided
if (empty($email)) {
    echo json_encode(["error" => "User email is required"]);
    exit();
}

// SQL query to fetch logs from the bookings, vehicle_expenses, vehicle_fillups, and emergency_service tables for the given email
$query = "
    SELECT 
        service_name AS type, booking_date AS date, service_price AS price
    FROM bookings
    WHERE user_email = :email AND status = 'completed'
    UNION ALL
    SELECT 
        expenses AS type, fill_date AS date, cost AS price
    FROM vehicle_expenses
    WHERE user_email = :email
    UNION ALL
    SELECT 
        quantity AS type, fill_date AS date, cost AS price
    FROM vehicle_fillups
    WHERE user_email = :email
    UNION ALL
    SELECT 
        service_needed AS type, request_time AS date, price
    FROM emergency_service
    WHERE user_email = :email AND status = 'completed'
    ORDER BY date DESC
";

try {
    // Prepare the query
    $stmt = $conn->prepare($query);
    
    // Bind the email parameter to prevent SQL injection
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the logs as a JSON response (even if empty)
    echo json_encode($logs ?: []); // Ensure the response is an array, even if empty
} catch (PDOException $e) {
    // If an error occurs, return an error message
    echo json_encode(["error" => "Failed to fetch logs: " . $e->getMessage()]);
}
?>
