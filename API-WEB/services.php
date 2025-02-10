<?php
include 'db.php';

header("Content-Type: application/json");

$sql = "SELECT * FROM services";
$result = $conn->query($sql);

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

echo json_encode($services);
?>
