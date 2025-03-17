<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require 'config.php'; // Ensure this file contains SMTP_USER and SMTP_PASS

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';

    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "Please enter your email."]);
        exit;
    }

    try {
        // Check if the email exists (using LOWER to avoid case issues)
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            echo json_encode(["status" => "error", "message" => "Email not found."]);
            exit;
        }

        // Generate a 6-digit random reset code
        $reset_code = rand(100000, 999999);
        $reset_expiry = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Code expires in 15 mins

        // Update the database with the reset code and expiry
        $stmt = $pdo->prepare("UPDATE admins SET reset_code = ?, reset_expiry = ? WHERE email = ?");
        $stmt->execute([$reset_code, $reset_expiry, $admin['email']]);

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER; 
            $mail->Password = SMTP_PASS; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(SMTP_USER, 'AutoLink Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body = "Your password reset code is: <b>$reset_code</b>. This code will expire in 15 minutes.";

            if ($mail->send()) {
                echo json_encode(["status" => "success", "message" => "Reset code sent to your email."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send email."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Email could not be sent. Error: " . $mail->ErrorInfo]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
