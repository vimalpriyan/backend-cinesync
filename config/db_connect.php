<?php
// --------------------------------------------------------
// CINE-SYNC: Robust Database Connection
// --------------------------------------------------------

// 1. Configuration
$servername = "127.0.0.1"; // IP is often faster/safer than 'localhost'
$username = "root";
$password = "";      
$dbname = "cinesync_db";
$port = 3306; // Your specific port

// 2. Enable strict error reporting for mysqli before connecting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 3. Create connection
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    // 4. Set Charset for Emojis
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // 5. CATCH CONNECTION ERRORS
    // Send valid JSON so the Android App handles it gracefully instead of crashing
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error", 
        "message" => "Database Connection Failed: " . $e->getMessage()
    ]);
    exit(); // Stop all execution
}