<?php
class BlockService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function blockUser($data) {
        // Support both route params (if used) and body params
        // Assuming body: { user_id: target_id } and Viewer ID from header (if possible) or body request
        // For simplicity adapting to existing patterns where viewer_id might be missing or in headers.
        
        // Let's assume we pass { user_id: target, blocker_id: me } from Android for now to be safe
        // Or get blocker_id from token if implemented. 
        // For this task, let's look for "blocker_id" in body or fall back to a known common param if any.
        
        // But Android `BlockUserRequest` only has `user_id`.
        // So we MUST get viewer ID from Headers/Token or add it to request.
        // Let's try to extract from Headers like UserController does.
        
        $blockerId = $this->getViewerId();
        $blockedId = isset($data->user_id) ? (int)$data->user_id : 0;

        if (!$blockerId || !$blockedId) {
             return ["status" => "error", "message" => "Missing User ID or Auth Token. Blocker: $blockerId, Blocked: $blockedId"];
        }

        if ($blockerId === $blockedId) {
            return ["status" => "error", "message" => "Cannot block yourself"];
        }

        $sql = "INSERT INTO blocked_users (blocker_id, blocked_id) VALUES ($blockerId, $blockedId)";
        if ($this->conn->query($sql)) {
            return ["status" => "success", "message" => "User blocked successfully"];
        } else {
             // 1062 is duplicate entry
            if ($this->conn->errno == 1062) {
                return ["status" => "success", "message" => "User already blocked"];
            }
            return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
        }
    }

    public function unblockUser($data) {
        $blockerId = $this->getViewerId();
        $blockedId = isset($data->user_id) ? (int)$data->user_id : 0;

        if (!$blockerId || !$blockedId) {
            return ["status" => "error", "message" => "Missing User ID"];
        }

        $sql = "DELETE FROM blocked_users WHERE blocker_id = $blockerId AND blocked_id = $blockedId";
        if ($this->conn->query($sql)) {
            return ["status" => "success", "message" => "User unblocked successfully"];
        }
        return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
    }
    
    public function getBlockedUsers($data) {
        // Can optionally take user_id from body or default to viewer
        $userId = isset($data->user_id) ? (int)$data->user_id : $this->getViewerId();
        
        if (!$userId) return ["status" => "error", "message" => "Missing User ID"];

        $sql = "
            SELECT u.user_id, u.username, u.full_name, u.profile_pic_url 
            FROM users u
            JOIN blocked_users b ON u.user_id = b.blocked_id
            WHERE b.blocker_id = $userId
        ";
        
        $result = $this->conn->query($sql);
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
             // Ensure types are strings for frontend consistency
             $row['user_id'] = (string)$row['user_id'];
             $users[] = $row;
        }
        
        return ["status" => "success", "users" => $users];
    }
    
    // Copy of getViewerId helper
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
}
?>