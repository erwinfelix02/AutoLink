<?php
include 'db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (isset($data->name) && isset($data->description) && isset($data->price)) {
    $name = $conn->real_escape_string($data->name);
    $description = $conn->real_escape_string($data->description);
    $price = $conn->real_escape_string($data->price);

    $sql = "INSERT INTO services (name, description, price) VALUES ('$name', '$description', '$price')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Service added successfully"]);
    } else {
        echo json_encode(["error" => "Failed to add service"]);
    }
} else {
    echo json_encode(["error" => "Invalid input"]);
}
?>
