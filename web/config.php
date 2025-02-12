<?php
// web/config.php

$host = "localhost";  // Database host (localhost for XAMPP)
$dbname = "autolink";  // Database name (shared between web and mobile)
$username = "root";  // Default username for XAMPP (root)
$password = "";  // Default password for XAMPP (empty)

try {
    // Create a PDO connection to the database
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
