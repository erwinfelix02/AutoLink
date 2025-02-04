<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'autolink';
$username = 'root';
$password = '';

// Create database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get user email
    $email = $_GET['email'];

    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "Email is required."]);
        exit();
    }

    // Fetch profile image URL from the database
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($imageUrl);
    $stmt->fetch();
    $stmt->close();

    if ($imageUrl) {
        echo json_encode(["success" => true, "imageUrl" => $imageUrl]);
    } else {
        echo json_encode(["success" => false, "message" => "No profile image found."]);
    }
}

$conn->close();
?>
