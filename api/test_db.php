<?php
// Test database connection
require_once __DIR__ . '/../config/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
} else {
    echo json_encode(["status" => "success", "message" => "Database connection successful"]);
}
?>