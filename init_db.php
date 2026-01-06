<?php
include __DIR__ . '/config/db_connect.php'; 

// Read the SQL file
$sqlFile = __DIR__ . '/setup_database.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found at $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Execute multi_query
if ($conn->multi_query($sql)) {
    do {
        // Prepare next result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    if ($conn->errno) {
        echo "Error executing SQL: " . $conn->error;
    } else {
        echo "Database tables checked/created successfully!";
    }
} else {
    echo "Error executing SQL: " . $conn->error;
}

$conn->close();
?>
