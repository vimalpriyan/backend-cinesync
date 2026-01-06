<?php
include __DIR__ . '/api/db.php'; 

// 1. conversation_favorites
$sql1 = "CREATE TABLE IF NOT EXISTS conversation_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    conversation_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, conversation_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
)";

if ($conn->query($sql1) === TRUE) {
    echo "Table conversation_favorites created successfully\n";
} else {
    echo "Error creating conversation_favorites: " . $conn->error . "\n";
}

// 2. blocked_users
$sql2 = "CREATE TABLE IF NOT EXISTS blocked_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_block (blocker_id, blocked_id),
    FOREIGN KEY (blocker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql2) === TRUE) {
    echo "Table blocked_users created successfully\n";
} else {
    echo "Error creating blocked_users: " . $conn->error . "\n";
}
?>
