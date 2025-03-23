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

// Query to fetch regular bookings (excluding completed/cancelled)
$sqlBookings = "SELECT 
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
                AND LOWER(b.status) NOT IN ('completed', 'cancelled')
                ORDER BY b.booking_date ASC";

// Query to fetch emergency service requests
$sqlEmergency = "SELECT 
                    emergency_id AS booking_id,
                    service_needed AS service_name,
                    price AS service_price,  
                    other_info AS service_description,
                    status,
                    request_time AS booking_date,
                    request_time AS booking_time,
                    'emergency.jpg' AS image_url
                FROM emergency_service
                WHERE user_email = :email 
                AND LOWER(status) NOT IN ('completed', 'cancelled')
                ORDER BY request_time ASC";

try {
    // Fetch regular bookings
    $stmt = $conn->prepare($sqlBookings);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch emergency service requests
    $stmt = $conn->prepare($sqlEmergency);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $emergencyServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge both results
    $allBookings = array_merge($bookings, $emergencyServices);

    // Base URLs
    $base_url = "http://localhost/AutoLink/web/uploads/";
    $emergency_image_url = "http://localhost/AutoLink/mobile/emergency.jpg"; // Change to match your web-accessible path

    foreach ($allBookings as &$booking) {
        // Ensure values are properly formatted
        $booking['service_name'] = htmlspecialchars($booking['service_name'] ?? "No Service Name");
        $booking['service_price'] = number_format((float)($booking['service_price'] ?? 0), 2, '.', '');
        $booking['service_description'] = htmlspecialchars($booking['service_description'] ?? "No Description");
        $booking['status'] = htmlspecialchars($booking['status'] ?? "Unknown");

            // Handle image URLs
        if (!empty($booking['image_url']) && $booking['image_url'] === 'emergency.jpg') {
            // Use predefined emergency image for emergency service requests
            $booking['image_url'] = "http://localhost/AutoLink/mobile/emergency.jpg"; 
        } elseif (!empty($booking['image_url'])) {
            $imagePath = ltrim($booking['image_url'], '/');
            if (!str_starts_with($imagePath, "uploads/")) {
                $imagePath = "uploads/" . $imagePath;
            }
            $booking['image_url'] = $base_url . rawurlencode(basename($imagePath));
        } else {
            // If no image is provided, use a default image
            $booking['image_url'] = $base_url . "default.jpg"; 
        }

        // Format booking_date and booking_time
        if (!empty($booking['booking_date'])) {
            $created_at = new DateTime($booking['booking_date']);
            $booking['booking_date'] = $created_at->format('Y-m-d'); // Format as date

            if (!empty($booking['booking_time'])) {
                $booking['booking_time'] = (new DateTime($booking['booking_time']))->format('h:i A'); // 12-hour format with AM/PM
            } else {
                $booking['booking_time'] = 'N/A';
            }
        } else {
            $booking['booking_date'] = 'N/A';
            $booking['booking_time'] = 'N/A';
        }
    }

    // Return response as JSON
    echo json_encode(["success" => true, "data" => $allBookings], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
