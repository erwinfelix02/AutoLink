<?php
require 'config.php'; // Ensure this path is correct

header("Content-Type: application/json");

// Validate the email parameter
if (!isset($_GET['user_email']) || empty($_GET['user_email'])) {
    echo json_encode(["success" => false, "message" => "User email is required"]);
    exit;
}

$user_email = trim($_GET['user_email']);

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

// Debugging logs
error_log("🔍 Searching completed bookings for: " . $user_email);

// Check database connection
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// SQL Query: Fetch completed bookings for the user
$sql = "SELECT 
            b.booking_id,
            b.service_name,
            b.booking_date,
            b.booking_time,
            b.status,
            s.image_url AS service_image
        FROM bookings b
        LEFT JOIN services s ON b.service_name = s.name
        WHERE b.status = 'completed' AND b.user_email = :user_email
        ORDER BY b.booking_date ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':user_email', $user_email, PDO::PARAM_STR);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define base URL for images
    $base_url = "http://localhost/AutoLink/web/uploads/";

    if (empty($bookings)) {
        error_log("❌ No completed bookings found for: " . $user_email);
        echo json_encode(["success" => false, "message" => "No completed bookings found"]);
        exit;
    }

    // Process bookings data
    foreach ($bookings as &$booking) {
        // Ensure values are properly formatted
        $booking['service_name'] = htmlspecialchars($booking['service_name'] ?? "No Service Name");
       // Convert status to "Completed" if it's "completed"
        $booking['status'] = ucfirst(strtolower(htmlspecialchars($booking['status'] ?? "Unknown")));

        // Handle the image URL
        if (!empty($booking['service_image'])) {
            $imagePath = ltrim($booking['service_image'], '/');
            if (!str_starts_with($imagePath, "uploads/")) {
                $imagePath = "uploads/" . $imagePath;
            }
            $booking['image_url'] = $base_url . rawurlencode(basename($imagePath));
        } else {
            $booking['image_url'] = $base_url . "default.jpg"; // Default image if no image found
        }

        // Format booking date and time
        if (!empty($booking['booking_date'])) {
            $booking['booking_date'] = (new DateTime($booking['booking_date']))->format('Y-m-d');
        } else {
            $booking['booking_date'] = 'N/A';
        }

        if (!empty($booking['booking_time'])) {
            $booking['booking_time'] = (new DateTime($booking['booking_time']))->format('H:i');
        } else {
            $booking['booking_time'] = 'N/A';
        }

        // Remove raw service_image column
        unset($booking['service_image']);
    }

    error_log("✅ Found " . count($bookings) . " completed bookings.");
    echo json_encode(["success" => true, "data" => $bookings], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log("❌ Database error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
