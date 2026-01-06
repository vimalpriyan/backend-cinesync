<?php
require_once __DIR__ . '/../models/UserService.php';

class UserController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Helper: Extract User ID from Token
    private function getViewerId() {
        $headers = null;
        if (function_exists('getallheaders')) { $headers = getallheaders(); } 
        else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (!empty($authHeader) && preg_match('/Bearer\s(\d+)/', $authHeader, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }

    // Get specific user profile
    public function get_user_profile() {
        $targetId = $_GET['user_id'] ?? json_decode(file_get_contents("php://input"))->user_id ?? null;
        
        // [FIX] Get the ID of the person viewing the profile
        $viewerId = $this->getViewerId();

        if (!$targetId) {
            echo json_encode(["status" => "error", "message" => "Target User ID required"]);
            return;
        }

        $service = new UserService($this->conn);
        // [FIX] Pass both IDs to the service
        $data = $service->getUserProfile($targetId, $viewerId);

        if ($data) {
            echo json_encode(["status" => "success", "user" => $data]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }
    }

    // ... (Keep getFollowers, getFollowing, searchUsers same as before) ...
    public function getFollowers() {
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) { echo json_encode(["status" => "error", "message" => "User ID required"]); return; }
        echo json_encode((new UserService($this->conn))->getFollowers($userId));
    }

    public function getFollowing() {
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) { echo json_encode(["status" => "error", "message" => "User ID required"]); return; }
        echo json_encode((new UserService($this->conn))->getFollowing($userId)); 
    }

    public function searchUsers() {
        $query = $_GET['query'] ?? '';
        echo json_encode((new UserService($this->conn))->searchUsers($query));
    }
}
?>