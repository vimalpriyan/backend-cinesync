<?php
include 'config/db_connect.php';

// 1. Add settings columns to users
$columns = [
    "language VARCHAR(20) DEFAULT 'English'",
    "personalized_ads TINYINT(1) DEFAULT 1",
    "ad_partners TINYINT(1) DEFAULT 1",
    "data_sharing TINYINT(1) DEFAULT 0"
];

foreach ($columns as $colDef) {
    $sql = "ALTER TABLE users ADD COLUMN $colDef";
    if ($conn->query($sql) === TRUE) {
        echo "Column added: $colDef\n";
    } else {
        if ($conn->errno == 1060) {
            echo "Column already exists: " . substr($colDef, 0, strpos($colDef, ' ')) . "\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    }
}

// 2. Create login_activities table
$sqlCreateLoginActivity = "CREATE TABLE IF NOT EXISTS login_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_name VARCHAR(100),
    ip_address VARCHAR(50),
    location VARCHAR(100),
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sqlCreateLoginActivity) === TRUE) {
    echo "Table login_activities created/exists\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
