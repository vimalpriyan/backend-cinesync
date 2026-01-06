<?php
require_once __DIR__ . '/../models/MessageService.php';

class MessageController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function send() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new MessageService($this->conn))->sendMessage($data));
    }

    public function get() {
        $action = $_GET['action'] ?? '';
        $data = json_decode(file_get_contents("php://input"));
        $service = new MessageService($this->conn);
        
        if ($action === 'get_conversations') {
            echo json_encode($service->getConversations($data));
        } else {
            echo json_encode($service->getConversation($data));
        }
    }

    public function toggle_favorite() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new MessageService($this->conn))->toggleFavorite($data));
    }

    public function add_reaction() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new MessageService($this->conn))->addReaction($data));
    }
}
?>