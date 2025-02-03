<?php
// Enable CORS if necessary
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Database connection (replace with your DB settings)
$host = "localhost";
$db_user = "root";  
$db_pass = "";     
$db_name = "autolink"; // Database name

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

// Check if all the required fields are provided
if (isset($data->car_make) && isset($data->car_model) && isset($data->fuel_type) && isset($data->license_no) && isset($data->year) && isset($data->tank_capacity) && isset($data->engine_power) && isset($data->user_email) && isset($data->user_name)) {

    // Sanitize the input data
    $car_make = $conn->real_escape_string($data->car_make);
    $car_model = $conn->real_escape_string($data->car_model);
    $fuel_type = $conn->real_escape_string($data->fuel_type);
    $license_no = $conn->real_escape_string($data->license_no);
    $year = $conn->real_escape_string($data->year);
    $tank_capacity = $conn->real_escape_string($data->tank_capacity);
    $engine_power = $conn->real_escape_string($data->engine_power);
    $user_email = $conn->real_escape_string($data->user_email); 
    $user_name = $conn->real_escape_string($data->user_name); 

    // Prepare the SQL query to insert the car data into the database
    $stmt = $conn->prepare("INSERT INTO car_models (car_make, car_model, fuel_type, license_no, year, tank_capacity, engine_power, user_email, user_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissss", $car_make, $car_model, $fuel_type, $license_no, $year, $tank_capacity, $engine_power, $user_email, $user_name);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Car data added successfully."));
    } else {
        echo json_encode(array("success" => false, "message" => "Failed to add car data."));
    }
    $stmt->close();
} else {
    
    echo json_encode(array("success" => false, "message" => "Please provide all the required fields."));
}

// Close the database connection
$conn->close();
?>
