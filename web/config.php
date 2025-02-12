<?php
// web/config.php
$host = "localhost"; // Database host (localhost if using XAMPP)
$dbname = "autolink"; // Database name (shared between web and mobile)
$username = "root"; // Default username sa XAMPP (root)
$password = ""; // Default XAMPP password (empty)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
