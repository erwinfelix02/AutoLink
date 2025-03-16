<?php
// web/config.php
define('SMTP_USER', 'christianmondala26@gmail.com');
define('SMTP_PASS', 'ocmy yziy wqxw ibgd');
$host = "localhost"; 
$dbname = "autolink"; 
$username = "root"; 
$password = ""; 


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
