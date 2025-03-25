<?php
//mobile/config
define('SMTP_USER', 'christianmondala26@gmail.com');
define('SMTP_PASS', 'ocmy yziy wqxw ibgd');
$host = "localhost"; // Change if using a remote database
$dbname = "autolink"; // Your database name
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
