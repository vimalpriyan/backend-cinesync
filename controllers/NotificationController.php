<?php
require_once __DIR__ . '/../models/NotificationService.php';

class NotificationController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function get() {
        $data = json_decode(file_get_contents("php://input"));
        // Fallback for GET param
        if (!$data) { $data = (object)['user_id' => $_GET['user_id'] ?? 0]; }
        
        echo json_encode((new NotificationService($this->conn))->getAndMarkAsRead($data));
    }
    
    public function getUnreadCount() {
        $userId = $_GET['user_id'] ?? 0;
        echo json_encode((new NotificationService($this->conn))->getUnreadCount($userId));
    }
}
?>