<?php
// FILE: api/upload.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = array();
$upload_dir = __DIR__ . '/../uploads/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file'])) {
    $file_name = time() . '_' . basename($_FILES['file']['name']);
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // NOTE: Change 10.0.2.2 to your specific IP if not using Emulator
        $url = "http://10.36.249.194:8012/cinesync/uploads/" . $file_name;
        echo json_encode(["status" => "success", "url" => $url]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file received."]);
}
?>