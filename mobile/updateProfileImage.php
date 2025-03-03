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

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user email
    $email = $_POST['email'];

    // Check if email is provided
    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "Email is required."]);
        exit();
    }

    // Check if file is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'profile_images/'; // Folder where images will be stored

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validate file type (Only allow JPG, PNG)
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['profile_image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Only JPG and PNG files are allowed."]);
            exit();
        }

        // Generate a unique filename
        $fileName = time() . '-' . basename($_FILES['profile_image']['name']);
        $uploadPath = $uploadDir . $fileName;

        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
            $imageUrl = "http://localhost/AutoLink/$uploadPath";
            // Update user's profile image in the database
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE email = ?");
            $stmt->bind_param("ss", $imageUrl, $email);
            
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Profile image uploaded successfully.", "imageUrl" => $imageUrl]);
            } else {
                echo json_encode(["success" => false, "message" => "Database update failed."]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No file uploaded or there was an error."]);
    }
}

$conn->close();
?>
