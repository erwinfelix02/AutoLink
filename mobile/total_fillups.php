<?php
header("Content-Type: application/json");
require 'config.php';

// Check if user_email is provided in the request
if (isset($_GET['user_email'])) {
    $user_email = $_GET['user_email']; // Get user_email from the request

    // Query to count the number of fill-ups for the given user in the vehicle_fillups table
    $query = "SELECT COUNT(*) AS total_fillups FROM vehicle_fillups WHERE user_email = :user_email";
    
    try {
        // Prepare the statement
        $stmt = $conn->prepare($query);
        
        // Bind the email parameter to the query
        $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        
        // Execute the query
        $stmt->execute();
        
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if any result is returned
        if ($result) {
            $total_fillups = $result['total_fillups'];
            // Output the result as JSON
            echo json_encode(['success' => true, 'count' => $total_fillups]);
        } else {
            // If no fill-ups are found for the user, still return success: true with count 0
            echo json_encode(['success' => true, 'count' => 0]);
        }

        // Close the statement
        $stmt = null;
    } catch (PDOException $e) {
        // If an error occurs, return a detailed error message
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // If user_email is not provided in the request, return an error
    echo json_encode(['success' => false, 'message' => 'User email is missing in the request.']);
}

$conn = null;
?>
