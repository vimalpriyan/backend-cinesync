<?php
require_once __DIR__ . '/../models/LikeService.php';

class LikeController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function toggle() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new LikeService($this->conn))->toggleLike($data));
    }
}
?>