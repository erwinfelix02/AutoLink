<?php
require 'config.php';
header("Content-Type: application/json");

// Get parameters
$user_email = $_GET['user_email'] ?? '';
$vehicle_make = $_GET['vehicle_make'] ?? '';
$vehicle_model = $_GET['vehicle_model'] ?? '';

if (empty($user_email) || empty($vehicle_make) || empty($vehicle_model)) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit();
}

try {
    // Query for fuel statistics
    $query = "
        SELECT 
            ROUND(AVG(odometer), 2) AS averageEfficiency, 
            ROUND(AVG(quantity), 2) AS quantityPerFillup,
            ROUND(AVG(cost), 2) AS costPerFillup,
            ROUND(AVG(price), 2) AS averagePricePerLtr,
            ROUND(SUM(cost), 2) + ROUND(AVG(price), 2) AS totalCost,  -- Corrected total cost calculation
            MAX(fill_date) AS lastUpdated
        FROM vehicle_fillups
        WHERE user_email = :user_email 
        AND vehicle_make = :vehicle_make 
        AND vehicle_model = :vehicle_model
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_email', $user_email);
    $stmt->bindParam(':vehicle_make', $vehicle_make);
    $stmt->bindParam(':vehicle_model', $vehicle_model);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query for total fill-ups (all-time count)
    $queryTotalFillups = "
        SELECT COUNT(*) AS totalFillups
        FROM vehicle_fillups
        WHERE user_email = :user_email
        AND vehicle_make = :vehicle_make
        AND vehicle_model = :vehicle_model
    ";

    $stmtTotalFillups = $conn->prepare($queryTotalFillups);
    $stmtTotalFillups->bindParam(':user_email', $user_email);
    $stmtTotalFillups->bindParam(':vehicle_make', $vehicle_make);
    $stmtTotalFillups->bindParam(':vehicle_model', $vehicle_model);
    $stmtTotalFillups->execute();
    $totalFillupsResult = $stmtTotalFillups->fetch(PDO::FETCH_ASSOC);
    $totalFillups = $totalFillupsResult['totalFillups'] ?? 0;

    // Query for fill-ups per month
    $queryFillups = "
        SELECT 
            COUNT(*) AS fillupCount,
            MONTH(fill_date) AS month,
            YEAR(fill_date) AS year
        FROM vehicle_fillups
        WHERE user_email = :user_email
        AND vehicle_make = :vehicle_make
        AND vehicle_model = :vehicle_model
        GROUP BY year, month
        ORDER BY year DESC, month DESC
    ";

    $stmtFillups = $conn->prepare($queryFillups);
    $stmtFillups->bindParam(':user_email', $user_email);
    $stmtFillups->bindParam(':vehicle_make', $vehicle_make);
    $stmtFillups->bindParam(':vehicle_model', $vehicle_model);
    $stmtFillups->execute();

    $monthlyFillups = [];
    while ($row = $stmtFillups->fetch(PDO::FETCH_ASSOC)) {
        $monthlyFillups[] = [
            "month" => (int)$row['month'],
            "year" => (int)$row['year'],
            "fillupCount" => (int)$row['fillupCount']
        ];
    }

    // If data is found, return JSON response
    if ($result && $result['averageEfficiency'] !== null) {
        echo json_encode([
            "success" => true,
            "data" => [
                "averageEfficiency" => round($result['averageEfficiency'], 2),
                "quantityPerFillup" => round($result['quantityPerFillup'], 2),
                "costPerFillup" => round($result['costPerFillup'], 2),
                "averagePricePerLtr" => round($result['averagePricePerLtr'], 2),
                "totalCost" => round($result['totalCost'], 2),
                "lastUpdated" => $result['lastUpdated'] ?: "N/A",
                "totalFillups" => $totalFillups,
                "monthlyFillups" => $monthlyFillups
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No data found for the specified vehicle."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
