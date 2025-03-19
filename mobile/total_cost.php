<?php
header("Content-Type: application/json");
require 'config.php';

// Check if user_email is provided in the request
if (isset($_GET['user_email']) && !empty($_GET['user_email'])) {
    $user_email = $_GET['user_email']; // Get user_email from the request

    // Query to fetch all costs for the given user
    $query = "SELECT cost FROM vehicle_fillups WHERE user_email = :user_email";

    try {
        // Prepare the statement
        $stmt = $conn->prepare($query);

        // Bind the email parameter to the query
        $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        // Fetch all the result rows
        $costs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If there are costs
        if ($costs) {
            // Sum up all the cost values
            $total_cost = 0;
            foreach ($costs as $cost) {
                // Ensure the cost is a valid number and add it to the total
                if (is_numeric($cost['cost'])) {
                    $total_cost += (float)$cost['cost']; // Convert to float for summation
                }
            }

            // Output the result as JSON with the totalcost field as a double
            echo json_encode(['success' => true, 'totalcost' => round($total_cost, 2)]);
        } else {
            // If no costs are found for the user
            echo json_encode(['success' => true, 'totalcost' => 0.0]);
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
