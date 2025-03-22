<?php
header("Content-Type: application/json");
require 'config.php';

if (isset($_GET['user_email'])) {
    $userEmail = $_GET['user_email'];

    // Initialize the total count
    $totalCount = 0;

    try {
        // Query to get the count of completed bookings for the user
        $bookingsQuery = "SELECT COUNT(*) as bookings_count FROM bookings WHERE user_email = ? AND LOWER(status) = 'completed'";
        $bookingsStmt = $conn->prepare($bookingsQuery);
        $bookingsStmt->bindParam(1, $userEmail);
        $bookingsStmt->execute();
        $bookingsRow = $bookingsStmt->fetch(PDO::FETCH_ASSOC);
        $totalCount += $bookingsRow['bookings_count'] ?? 0;

        // Query to get the count of completed emergency services for the user
        $emergencyQuery = "SELECT COUNT(*) as emergency_count FROM emergency_service WHERE user_email = ? AND LOWER(status) = 'completed'";
        $emergencyStmt = $conn->prepare($emergencyQuery);
        $emergencyStmt->bindParam(1, $userEmail);
        $emergencyStmt->execute();
        $emergencyRow = $emergencyStmt->fetch(PDO::FETCH_ASSOC);
        $totalCount += $emergencyRow['emergency_count'] ?? 0;

        // Return the total count as JSON response
        echo json_encode(array(
            'success' => true,
            'count' => $totalCount
        ));
    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Error fetching data: ' . $e->getMessage()
        ));
    }
} else {
    // If user_email is not set, return an error message
    echo json_encode(array(
        'success' => false,
        'message' => 'User email is required.'
    ));
}
?>
