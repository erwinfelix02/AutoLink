<?php
header('Content-Type: application/json');
require '../config.php';


// Create a new PDO instance for database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $fullname, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception handling for errors
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Query to get all bookings (appointments) from the database
$query = "SELECT b.booking_id, b.user_id, b.service_name, b.service_price, b.service_description, b.booking_date, b.booking_time, b.status, u.user_name, u.user_email
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          ORDER BY b.booking_date DESC"; // Order by booking date

// Prepare the SQL statement
$stmt = $conn->prepare($query);

// Execute the query
$stmt->execute();

// Fetch all the results
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are any bookings
if ($bookings) {
    echo json_encode([
        'success' => true,
        'data' => $bookings
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No bookings found.'
    ]);
}
?>
