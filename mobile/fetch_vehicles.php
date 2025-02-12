<?php
require 'config.php'; // Include database connection

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['email'])) { // Change from user_id to email
        echo json_encode(["error" => "Missing email"]);
        http_response_code(400); // Bad Request
        exit;
    }

    $email = $_GET['email']; // Get user email from request

    try {
        // Update the query to filter by email instead of user_id
        $query = "SELECT * FROM vehicles WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($vehicles);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
}
?>
