<?php
include 'config/db_connect.php'; 

$sql = "ALTER TABLE users ADD COLUMN notifications_enabled TINYINT(1) DEFAULT 1";

if ($conn->query($sql) === TRUE) {
    echo "Column notifications_enabled added successfully\n";
} else {
    // Check if error is "Duplicate column name"
    if ($conn->errno == 1060) {
        echo "Column notifications_enabled already exists\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}
?>
