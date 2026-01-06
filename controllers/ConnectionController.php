<?php
require_once __DIR__ . '/../models/ConnectionService.php';

class ConnectionController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // SEND Request
    // SEND Request
    public function send_request() {
        $data = json_decode(file_get_contents("php://input"));
        
        // FIX: Check for 'sender_id' OR 'requester_id' to match your App's JSON
        $senderId = $data->sender_id ?? $data->requester_id ?? null;
        $receiverId = $data->receiver_id ?? null;

        if (!$senderId || !$receiverId) {
            echo json_encode(["status" => "error", "message" => "Missing IDs (Received: " . print_r($data, true) . ")"]);
            return;
        }

        $service = new ConnectionService($this->conn);
        echo json_encode($service->sendRequest($senderId, $receiverId));
    }

    // ACCEPT Request
    public function accept_request() {
        $data = json_decode(file_get_contents("php://input"));
        // We need the Connection ID (request_id) and the User accepting it (for security)
        if (!isset($data->request_id) || !isset($data->user_id)) {
            echo json_encode(["status" => "error", "message" => "Missing Request ID or User ID"]);
            return;
        }
        $service = new ConnectionService($this->conn);
        echo json_encode($service->acceptRequest($data->request_id, $data->user_id));
    }

    // GET PENDING Requests
    public function get_requests() {
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(["status" => "error", "message" => "User ID required"]);
            return;
        }
        $service = new ConnectionService($this->conn);
        echo json_encode($service->getPendingRequests($userId));
    }

    // CHECK STATUS (For UI)
    public function check_status() {
        $myId = $_GET['my_id'] ?? null;
        $targetId = $_GET['target_id'] ?? null;
        
        if (!$myId || !$targetId) {
            echo json_encode(["status" => "error", "message" => "IDs required"]);
            return;
        }
        $service = new ConnectionService($this->conn);
        echo json_encode($service->checkStatus($myId, $targetId));
    }
    // RESPOND TO REQUEST (Accept/Reject)
    public function respond_request() {
        $data = json_decode(file_get_contents("php://input"));

        // Match the JSON keys from your log: {"requester_id":"32","receiver_id":"37","status":"accepted"}
        $requesterId = $data->requester_id ?? null;
        $receiverId = $data->receiver_id ?? null;
        $status = $data->status ?? null;

        if (!$requesterId || !$receiverId || !$status) {
            echo json_encode([
                "status" => "error", 
                "message" => "Missing fields. Required: requester_id, receiver_id, status"
            ]);
            return;
        }

        $service = new ConnectionService($this->conn);
        echo json_encode($service->respondToRequest($requesterId, $receiverId, $status));
    }

    // DELETE CONNECTION
    public function delete_connection() {
         $data = json_decode(file_get_contents("php://input"));
         $user1 = $data->user_id_1 ?? null;
         $user2 = $data->user_id_2 ?? null;

         if (!$user1 || !$user2) {
             echo json_encode(["status" => "error", "message" => "Missing user IDs"]);
             return;
         }
         
         $service = new ConnectionService($this->conn);
         echo json_encode($service->deleteConnection($user1, $user2));
    }
    
}
?>