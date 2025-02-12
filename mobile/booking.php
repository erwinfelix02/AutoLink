<?php
// Set the content type to JSON
header('Content-Type: application/json');
require 'config.php';

// Create a new PDO instance for database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception handling for errors
} catch (PDOException $e) {
    // Catch connection errors and return a failure response
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Get the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required fields are present in the request
if (
    isset($data['userId'], $data['serviceName'], $data['servicePrice'], $data['serviceDescription'], 
    $data['bookingDate'], $data['bookingTime'], $data['inclusions'], $data['userEmail'], $data['userName'])
) {
    // Extracting the booking data from the request
    $userId = $data['userId'];
    $serviceName = $data['serviceName'];
    $servicePrice = $data['servicePrice'];
    $serviceDescription = $data['serviceDescription'];
    $bookingDate = $data['bookingDate'];
    $bookingTime = $data['bookingTime'];
    $inclusions = $data['inclusions'];
    $userEmail = $data['userEmail'];
    $userName = $data['userName'];

    // Validate the date format (assuming the format is YYYY-MM-DD)
    $dateFormat = 'Y-m-d';
    $d = DateTime::createFromFormat($dateFormat, $bookingDate);
    if ($d && $d->format($dateFormat) === $bookingDate) {
        // The date is valid
    } else {
        // If the date is invalid, return an error response
        echo json_encode([
            'success' => false,
            'message' => 'Invalid date format. Please use YYYY-MM-DD.'
        ]);
        exit;
    }

    // Prepare an SQL statement to insert the booking data into the database
    $query = "INSERT INTO bookings (user_id, service_name, service_price, service_description, booking_date, booking_time, inclusions, user_email, user_name)
              VALUES (:userId, :serviceName, :servicePrice, :serviceDescription, :bookingDate, :bookingTime, :inclusions, :userEmail, :userName)";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters to the prepared statement
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':serviceName', $serviceName);
    $stmt->bindParam(':servicePrice', $servicePrice);
    $stmt->bindParam(':serviceDescription', $serviceDescription);
    $stmt->bindParam(':bookingDate', $bookingDate);
    $stmt->bindParam(':bookingTime', $bookingTime);
    $stmt->bindParam(':inclusions', $inclusions);
    $stmt->bindParam(':userEmail', $userEmail);
    $stmt->bindParam(':userName', $userName);

    // Execute the query
    if ($stmt->execute()) {
        // If insertion is successful, get the ID of the inserted booking
        $bookingId = $conn->lastInsertId();
        
        // Respond with success and booking details
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed',
            'data' => [
                [
                    'bookingId' => $bookingId,
                    'serviceName' => $serviceName,
                    'bookingTime' => $bookingTime,
                    'userEmail' => $userEmail
                ]
            ]
        ]);
    } else {
        // If insertion fails, respond with failure message
        echo json_encode([
            'success' => false,
            'message' => 'Booking failed, unable to insert data into the database'
        ]);
    }
} else {
    // If any of the required fields are missing, respond with failure
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields in the request'
    ]);
}
?>
