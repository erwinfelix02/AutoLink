<?php
require 'config.php'; // Ensure the path is correct

header("Content-Type: application/json");

if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

$email = trim($_GET['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Fetch bookings and exclude "Completed" and "Cancelled" statuses
$sql = "SELECT 
            b.booking_id,
            b.service_name,
            b.service_price,
            b.service_description,  
            b.status,
            b.booking_date,
            b.booking_time,
            s.image_url
        FROM bookings b
        LEFT JOIN services s ON b.service_name = s.name
        WHERE b.user_email = :email 
        AND LOWER(b.status) NOT IN ('completed', 'cancelled')  -- Exclude Completed and Cancelled bookings
        ORDER BY b.booking_date ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Base URL for image handling
    $base_url = "http://localhost/AutoLink/web/uploads/";

    foreach ($bookings as &$booking) {
        // Ensure no empty values are returned for required fields
        $booking['service_name'] = htmlspecialchars($booking['service_name'] ?? "No Service Name");
        $booking['service_price'] = number_format((float)($booking['service_price'] ?? 0), 2, '.', '');
        $booking['service_description'] = htmlspecialchars($booking['service_description'] ?? "No Description");
        $booking['status'] = htmlspecialchars($booking['status'] ?? "Unknown");

        // Handle the image URL
        if (!empty($booking['image_url'])) {
            $imagePath = ltrim($booking['image_url'], '/');
            if (!str_starts_with($imagePath, "uploads/")) {
                $imagePath = "uploads/" . $imagePath;
            }
            $booking['image_url'] = $base_url . rawurlencode(basename($imagePath));
        } else {
            $booking['image_url'] = $base_url . "default.jpg"; // Default image if no image found
        }

        // Format booking_date and booking_time
        if (!empty($booking['booking_date'])) {
            $created_at = new DateTime($booking['booking_date']);
            $booking['booking_date'] = $created_at->format('Y-m-d'); // Format as date
            $booking['booking_time'] = (new DateTime($booking['booking_time']))->format('H:i'); // Format time
        } else {
            $booking['booking_date'] = 'N/A';
            $booking['booking_time'] = 'N/A';
        }
    }

    // Return response as JSON
    echo json_encode(["success" => true, "data" => $bookings], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
