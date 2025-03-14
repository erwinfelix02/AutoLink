<?php
header("Content-Type: application/json");
require 'config.php'; // Include database configuration file

// Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if JSON body is valid and contains necessary fields
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    try {
        // Check if the user exists in the database
        $stmt = $conn->prepare("SELECT id, full_name, email, password, refresh_token FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Fetch user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Generate new access token and refresh token
                $accessToken = generateJWT($user['id'], $user['email']);
                $refreshToken = generateRefreshToken();

                // Update the user's refresh token in the database
                $updateStmt = $conn->prepare("UPDATE users SET refresh_token = :refresh_token WHERE email = :email");
                $updateStmt->bindParam(':refresh_token', $refreshToken, PDO::PARAM_STR);
                $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $updateStmt->execute();

                // Respond with success, new access token, and refresh token
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'accessToken' => $accessToken,
                    'refreshToken' => $refreshToken,  // New refresh token
                    'userId' => $user['id'],
                    'fullName' => $user['full_name'],
                    'email' => $user['email']
                ]);
            } else {
                // Invalid password
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            // User not found
            echo json_encode(['success' => false, 'message' => 'Invalid Email or Password']);
        }
    } catch (Exception $e) {
        // Handle any other errors (generic error message)
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
        error_log($e->getMessage());
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Function to generate JWT token (Access Token)
function generateJWT($userId, $email) {
    $header = base64UrlEncode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload = base64UrlEncode(json_encode([
        'userId' => $userId,
        'email' => $email,
        'iat' => time(), // Issued at time
        'exp' => time() + 3600 // Expiration time (1 hour from now)
    ]));

    $signature = hash_hmac('sha256', "$header.$payload", 'your_secret_key', true);
    $signature = base64UrlEncode($signature);

    // Return the JWT token
    return "$header.$payload.$signature";
}

// Function to generate refresh token
function generateRefreshToken() {
    return bin2hex(random_bytes(64));  // Generate a random 64-byte token
}

// Function to base64 URL-safe encoding
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
