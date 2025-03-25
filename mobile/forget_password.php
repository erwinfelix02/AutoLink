<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require 'config.php'; // Ensure this file contains SMTP_USER and SMTP_PASS

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    try {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT email FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "Email not found."]);
            exit;
        }

        // Generate a 6-digit reset code & expiry time
        $reset_code = mt_rand(100000, 999999);
        $reset_expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Store the reset code in the database
        $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expiry = ? WHERE email = ?");
        $stmt->execute([$reset_code, $reset_expiry, $user['email']]);

        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'christianmondala26@gmail.com';
            $mail->Password = 'ocmy yziy wqxw ibgd';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(SMTP_USER, 'AutoLink Support');
            $mail->addAddress($email); 

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body = "Hello,<br><br>Your password reset code is: <b>$reset_code</b>.<br><br>This code will expire in 15 minutes.<br><br>Regards,<br>AutoLink Support";

            if ($mail->send()) {
                echo json_encode(["status" => "success", "message" => "Reset code sent to your email."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send email."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Mail error: " . $mail->ErrorInfo]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
