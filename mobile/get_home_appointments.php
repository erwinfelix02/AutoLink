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

// Query to fetch regular bookings
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

// Query to fetch emergency service requests (keeping emergency_id as emergency_id)
$sqlEmergency = "SELECT 
                    emergency_id,
                    'Emergency Service' AS service_name,
                    0 AS service_price,  
                    service_needed AS service_description,
                    status,
                    request_time AS booking_date,
                    request_time AS booking_time,
                    NULL AS image_url
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

    // Base URLs
    $base_url = "http://localhost/AutoLink/web/uploads/";
    $emergency_image_url = "http://localhost/AutoLink/mobile/emergency.jpg"; // Update if needed

    foreach ($bookings as &$booking) {
        // Ensure values are properly formatted
        $booking['service_name'] = htmlspecialchars($booking['service_name'] ?? "No Service Name");
        $booking['service_price'] = number_format((float)($booking['service_price'] ?? 0), 2, '.', '');
        $booking['service_description'] = htmlspecialchars($booking['service_description'] ?? "No Description");
        $booking['status'] = htmlspecialchars($booking['status'] ?? "Unknown");

        // Handle image URLs
        if (!empty($booking['image_url'])) {
            $imagePath = ltrim($booking['image_url'], '/');
            $booking['image_url'] = $base_url . rawurlencode(basename($imagePath));
        } else {
            $booking['image_url'] = $base_url . "default.jpg"; // Default image if none found
        }
    }

    foreach ($emergencyServices as &$emergency) {
        // Properly map emergency ID for cancellation
        $emergency['booking_id'] = $emergency['emergency_id']; 
        unset($emergency['emergency_id']); // Remove to avoid confusion

        // Format emergency fields
        $emergency['service_name'] = "Emergency Service";
        $emergency['service_price'] = "0.00";
        $emergency['service_description'] = htmlspecialchars($emergency['service_description'] ?? "No Description");
        $emergency['status'] = htmlspecialchars($emergency['status'] ?? "Unknown");
        $emergency['image_url'] = $emergency_image_url;

        // Format booking_date and booking_time
        if (!empty($emergency['booking_date'])) {
            $created_at = new DateTime($emergency['booking_date']);
            $emergency['booking_date'] = $created_at->format('Y-m-d'); // Format as date

            if (!empty($emergency['booking_time'])) {
                $emergency['booking_time'] = (new DateTime($emergency['booking_time']))->format('h:i A'); // 12-hour format
            } else {
                $emergency['booking_time'] = 'N/A';
            }
        } else {
            $emergency['booking_date'] = 'N/A';
            $emergency['booking_time'] = 'N/A';
        }
    }

    // Merge both results
    $allBookings = array_merge($bookings, $emergencyServices);

    // Return response as JSON
    echo json_encode(["success" => true, "data" => $allBookings], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
