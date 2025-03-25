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
error_log("🔍 Searching completed services for: " . $user_email);

// Check database connection
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// SQL Query: Fetch completed bookings
$sqlBookings = "SELECT 
                    b.booking_id AS id,
                    b.service_name,
                    b.booking_date,
                    b.booking_time,
                    b.status,
                    COALESCE(s.image_url, '') AS service_image
                FROM bookings b
                LEFT JOIN services s ON b.service_name = s.name
                WHERE LOWER(b.status) = 'completed' AND b.user_email = :user_email
                ORDER BY b.booking_date ASC";

// SQL Query: Fetch completed emergency services
$sqlEmergencies = "SELECT 
                    e.emergency_id AS id,
                    e.service_needed AS service_name, 
                    e.other_info AS description,
                    DATE(e.request_time) AS booking_date,
                    '' AS booking_time, 
                    e.status,
                    'emergency.jpg' AS service_image  -- Placeholder, will be replaced later
                FROM emergency_service e
                WHERE LOWER(e.status) = 'completed' AND e.user_email = :user_email
                ORDER BY e.request_time ASC";

try {
    // Fetch completed bookings
    $stmtBookings = $conn->prepare($sqlBookings);
    $stmtBookings->bindValue(':user_email', $user_email, PDO::PARAM_STR);
    $stmtBookings->execute();
    $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

    // Fetch completed emergency services
    $stmtEmergencies = $conn->prepare($sqlEmergencies);
    $stmtEmergencies->bindValue(':user_email', $user_email, PDO::PARAM_STR);
    $stmtEmergencies->execute();
    $emergencies = $stmtEmergencies->fetchAll(PDO::FETCH_ASSOC);

    // Merge both results
    $completedServices = array_merge($bookings, $emergencies);

    // Define base URLs for images
    $base_url_services = "http://localhost/AutoLink/web/uploads/";
    $emergency_image_url = "http://localhost/AutoLink/mobile/emergency.jpg"; // Emergency service image

    if (empty($completedServices)) {
        error_log("❌ No completed services found for: " . $user_email);
        echo json_encode(["success" => false, "message" => "No completed services yet"]);
        exit;
    }

    // Process services data
    foreach ($completedServices as &$service) {
        // Ensure values are properly formatted
        $service['service_name'] = htmlspecialchars($service['service_name'] ?? "No Service Name");

        // Convert status to "Completed"
        $service['status'] = ucfirst(strtolower(htmlspecialchars($service['status'] ?? "Unknown")));


            // Handle the image URL
        if (!empty($service['service_image']) && $service['service_image'] !== 'emergency.jpg') {
            // If a custom service image is provided (and not emergency.jpg), use it
            $imagePath = trim($service['service_image'], '/');
            $service['image_url'] = $base_url_services . rawurlencode(basename($imagePath));
        } else {
            // If service_image is 'emergency.jpg' or not provided, use the emergency image
            $service['image_url'] = $service['service_image'] === 'emergency.jpg'
                ? $emergency_image_url  // Use emergency image
                : $base_url_services . "default.jpg"; // Default image if no image found
        }


        // Format booking date and time
        $service['booking_date'] = !empty($service['booking_date']) 
            ? (new DateTime($service['booking_date']))->format('Y-m-d') 
            : 'N/A';

        $service['booking_time'] = !empty($service['booking_time']) 
            ? (new DateTime($service['booking_time']))->format('H:i') 
            : 'N/A';

        // Remove raw service_image column
        unset($service['service_image']);
    }

    error_log("✅ Found " . count($completedServices) . " completed services.");
    echo json_encode(["success" => true, "data" => $completedServices], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log("❌ Database error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
