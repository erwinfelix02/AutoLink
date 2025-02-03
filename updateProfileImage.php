<?php
// Enable CORS if necessary
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Database connection (replace with your DB settings)
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "autolink"; // Change to your actual database name

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the file is uploaded and the email is received
if (isset($_FILES['profile_image']) && isset($_POST['email'])) {
    $email = $_POST['email'];  // User email
    $image = $_FILES['profile_image']; // Uploaded image

    // Generate a unique file name
    $imageName = uniqid() . '-' . basename($image['name']);
    $targetDir = "uploads/profile_images/"; // Directory to store images
    $targetFile = $targetDir . $imageName;

    // Check if the image is an actual image or fake one
    if (getimagesize($image["tmp_name"]) !== false) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            // Store the new image URL in the database for the user
            $imageUrl = "https://your-server-url.com/" . $targetFile; // URL to access the image

            // Update the user's profile image URL in the database
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE email = ?");
            $stmt->bind_param("ss", $imageUrl, $email);
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->affected_rows > 0) {
                $response = array('success' => true, 'message' => $imageUrl);
                echo json_encode($response);
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update image.'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to upload image.'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'File is not a valid image.'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Email or image not provided.'));
}

// Close the database connection
$conn->close();
?>
