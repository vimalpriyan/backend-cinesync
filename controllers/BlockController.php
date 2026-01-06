<?php
require_once __DIR__ . '/../models/BlockService.php';

class BlockController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function block() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new BlockService($this->conn))->blockUser($data));
    }

    public function unblock() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new BlockService($this->conn))->unblockUser($data));
    }

    public function get_blocked_users() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new BlockService($this->conn))->getBlockedUsers($data));
    }
}
?>