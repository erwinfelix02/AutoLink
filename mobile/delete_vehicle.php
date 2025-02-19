<?php
$host = "localhost"; // Change if using a remote database
$dbname = "autolink"; // Your database name
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection error: " . $e->getMessage()]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "Vehicle ID is required"]);
        exit;
    }

    $id = intval($_POST['id']); 

    try {
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Vehicle deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "No vehicle found with this ID"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
