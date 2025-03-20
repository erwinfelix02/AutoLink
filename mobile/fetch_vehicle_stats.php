<?php
require 'config.php'; 
header("Content-Type: application/json");

$response = ["success" => false, "data" => []];

try {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (!isset($_GET["email"]) || empty($_GET["email"])) {
            throw new Exception("Email parameter is missing");
        }

        $email = $_GET["email"];

        // Query to fetch only `vehicle_make` and `vehicle_model`
        $query = "SELECT vehicle_make AS vehicleMake, vehicle_model AS vehicleModel 
                  FROM vehicle_fillups WHERE user_email = :email";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($vehicles) {
            $response["success"] = true;
            $response["data"] = $vehicles;
        } else {
            $response["message"] = "No vehicles found";
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>
