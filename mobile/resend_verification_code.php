<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure 'vendor/autoload.php' is correctly required
$autoloadPath = __DIR__ . '/../vendor/autoload.php'; // Adjusted path
if (!file_exists($autoloadPath)) {
    die(json_encode(["status" => "error", "message" => "Missing vendor/autoload.php. Run `composer install`."]));
}
require $autoloadPath;

// Ensure 'config.php' is included
$configPath = __DIR__ . '/config.php'; // Adjusted path to match 'mobile' folder structure
if (!file_exists($configPath)) {
    die(json_encode(["status" => "error", "message" => "Missing config.php file. Expected path: " . realpath(__DIR__ . '/config.php')]));
}
require $configPath;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set response headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Check if POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

// Validate email input
$email = trim($_POST['email'] ?? '');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid or missing email parameter."]);
    exit;
}

try {
    $verification_code = rand(100000, 999999);
    $expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Ensure $conn is defined in config.php
    if (!isset($conn)) {
        throw new Exception("Database connection is missing.");
    }

    $query = "UPDATE users SET reset_code = ?, reset_expiry = ? WHERE LOWER(email) = LOWER(?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bindParam(1, $verification_code, PDO::PARAM_STR);
    $stmt->bindParam(2, $expiry_time, PDO::PARAM_STR);
    $stmt->bindParam(3, $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Send email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // Secure credentials from config.php
        $mail->Username = 'christianmondala26@gmail.com';
        $mail->Password = 'ocmy yziy wqxw ibgd';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(SMTP_USER, 'AutoLink Support'); // Define in config.php
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Your Verification Code";
        $mail->Body = "Your verification code is: <b>$verification_code</b><br>This code expires in 10 minutes.";

        if ($mail->send()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Verification code sent successfully."]);
            exit;
        } else {
            throw new Exception("Failed to send email.");
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Email not found or no changes made."]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    exit;
}
?>
