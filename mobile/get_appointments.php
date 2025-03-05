<?php
require 'config.php'; 

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

// Fetch bookings and order by created_at (earliest first)
$sql = "SELECT 
            b.booking_id,
            b.service_name,
            b.service_price,
            b.service_description,  -- Added service description
            b.status,
            b.created_at,  
            s.image_url
        FROM bookings b
        LEFT JOIN services s ON b.service_name = s.name
        WHERE b.user_email = :email
        ORDER BY b.created_at ASC";  // Order by first booking

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Base URL updated for emulator compatibility
    $base_url = "http://localhost/AutoLink/web/uploads/";

    foreach ($bookings as &$booking) {
        $booking['service_name'] = htmlspecialchars($booking['service_name'] ?? "No Service Name");
        $booking['service_price'] = number_format((float)($booking['service_price'] ?? 0), 2, '.', '');
        $booking['service_description'] = htmlspecialchars($booking['service_description'] ?? "No Description");  // Handle null values
        $booking['status'] = htmlspecialchars($booking['status'] ?? "Unknown");

        // Ensure correct image URL formatting
        if (!empty($booking['image_url'])) {
            $imagePath = ltrim($booking['image_url'], '/');
            if (!str_starts_with($imagePath, "uploads/")) {
                $imagePath = "uploads/" . $imagePath;
            }
            $booking['image_url'] = $base_url . rawurlencode(basename($imagePath));
        } else {
            $booking['image_url'] = $base_url . "default.jpg"; // Default image
        }
    }

    echo json_encode(["success" => true, "data" => $bookings], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
