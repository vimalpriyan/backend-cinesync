<?php
include 'config/db_connect.php';

// 1. Add two_factor_enabled to users
$sqlAdd2FA = "ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0";
if ($conn->query($sqlAdd2FA) === TRUE) {
    echo "Column two_factor_enabled added successfully\n";
} else {
    if ($conn->errno == 1060) {
        echo "Column two_factor_enabled already exists\n";
    } else {
        echo "Error adding column 2FA: " . $conn->error . "\n";
    }
}

// 2. Create reports table
$sqlCreateReports = "CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sqlCreateReports) === TRUE) {
    echo "Table reports created successfully\n";
} else {
    echo "Error creating table reports: " . $conn->error . "\n";
}
?>
