<?php
class UserService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- GET USER PROFILE + COUNTS + CONNECTION STATUS ---
    public function getUserProfile($targetUserId, $viewerId = 0) {
        $targetUserId = (int)$targetUserId;
        $viewerId = (int)$viewerId;

        // 1. Get Basic User Info
        $sql = "SELECT user_id, username, email, full_name, bio, profile_pic_url, role, is_verified, created_at 
                FROM users WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $targetUserId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) return null;

        $user['user_id'] = (string)$user['user_id'];

        // 2. Connection Counts (CRITICAL: Only count 'accepted')
        $connSql = "SELECT COUNT(*) as total FROM connections 
                    WHERE (user_id_1 = ? OR user_id_2 = ?) 
                    AND status = 'accepted'"; // <--- This ensures pending requests don't increase the number
        $stmtConn = $this->conn->prepare($connSql);
        $stmtConn->bind_param("ii", $targetUserId, $targetUserId);
        $stmtConn->execute();
        $user['connections_count'] = (int)$stmtConn->get_result()->fetch_assoc()['total'];

        // 3. Post Counts
        $postSql = "SELECT COUNT(*) as total FROM posts WHERE user_id = ?";
        $stmtPost = $this->conn->prepare($postSql);
        $stmtPost->bind_param("i", $targetUserId);
        $stmtPost->execute();
        $user['posts_count'] = (int)$stmtPost->get_result()->fetch_assoc()['total'];

        // --- [CRITICAL FIX] DETERMINE CONNECTION STATUS ---
        
        if ($viewerId === 0 || $viewerId === $targetUserId) {
            $user['connection_status'] = 'self';
        } else {
            // Check if ANY relationship exists between Viewer and Target
            $relSql = "SELECT user_id_1, status FROM connections 
                       WHERE (user_id_1 = ? AND user_id_2 = ?) 
                          OR (user_id_1 = ? AND user_id_2 = ?)";
            
            $stmtRel = $this->conn->prepare($relSql);
            $stmtRel->bind_param("iiii", $viewerId, $targetUserId, $targetUserId, $viewerId);
            $stmtRel->execute();
            $resRel = $stmtRel->get_result();

            if ($row = $resRel->fetch_assoc()) {
                // HERE IS THE LOGIC FIX:
                if ($row['status'] === 'accepted') {
                    $user['connection_status'] = 'connected';
                } elseif ($row['status'] === 'pending') {
                    // Check who sent it:
                    // If Viewer (user_id_1) sent it -> 'sent'
                    // If Target (user_id_1) sent it -> 'received'
                    $user['connection_status'] = ($row['user_id_1'] == $viewerId) ? 'sent' : 'received';
                } else {
                    // rejected or blocked
                    $user['connection_status'] = 'none'; 
                }
            } else {
                $user['connection_status'] = 'none';
            }
        }
        // ---------------------------------------------------------

        return $user;
    }

    // --- GET FOLLOWERS LIST (Only Accepted) ---
    public function getFollowers($userId) {
        $sql = "SELECT u.user_id, u.username, u.full_name, u.profile_pic_url 
                FROM users u
                JOIN connections c ON (
                    (c.user_id_1 = ? AND c.user_id_2 = u.user_id) OR 
                    (c.user_id_2 = ? AND c.user_id_1 = u.user_id)
                )
                WHERE c.status = 'accepted'"; // Ensure only friends are listed

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $row['user_id'] = (string)$row['user_id']; 
            $users[] = $row;
        }
        return ["status" => "success", "users" => $users];
    }

    public function getFollowing($userId) {
        return $this->getFollowers($userId); 
    }
    
    public function searchUsers($query) {
        $searchTerm = "%" . $query . "%";
        $sql = "SELECT user_id, username, full_name, profile_pic_url 
                FROM users 
                WHERE username LIKE ? OR full_name LIKE ?
                LIMIT 20";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $row['user_id'] = (string)$row['user_id'];
            $users[] = $row;
        }
        return ["status" => "success", "users" => $users];
    }
}
?>