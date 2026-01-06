<?php
class NotificationService {
    private $conn;
    private $table = "notification";

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 1. CREATE NOTIFICATION ---
    public function create($userId, $type, $refId, $senderId) {
        if ($type === 'like' || $type === 'comment') {
            $check = $this->conn->prepare("SELECT notification_id FROM notification WHERE user_id=? AND sender_id=? AND type=? AND reference_id=?");
            $check->bind_param("iisi", $userId, $senderId, $type, $refId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) return false;
        }

        $sql = "INSERT INTO notification (user_id, sender_id, type, reference_id, is_read, created_at) 
                VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisi", $userId, $senderId, $type, $refId);
        return $stmt->execute();
    }

    // --- 2. FETCH NOTIFICATIONS & MARK READ ---
    public function getAndMarkAsRead($data) {
        $uid = 0;
        if (isset($data->user_id)) $uid = (int)$data->user_id;
        elseif (isset($_GET['user_id'])) $uid = (int)$_GET['user_id'];

        if ($uid === 0) return ["status" => "error", "message" => "Missing User ID", "notifications" => []];

        $sql = "SELECT n.notification_id, n.user_id, n.sender_id, n.type, n.reference_id, n.is_read, n.created_at, 
                       u.username, u.profile_pic_url 
                FROM " . $this->table . " n
                JOIN users u ON n.sender_id = u.user_id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifs = [];
        while ($row = $result->fetch_assoc()) {
            // [CRITICAL FIX] Cast ALL IDs to String for Android
            $row['notification_id'] = (string)$row['notification_id'];
            $row['user_id']         = (string)$row['user_id'];
            $row['sender_id']       = (string)$row['sender_id'];
            
            // *** THIS WAS MISSING ***
            $row['reference_id']    = (string)$row['reference_id']; 
            
            $row['is_read']         = (string)$row['is_read'];
            
            $notifs[] = $row;
        }

        // Mark as read
        $this->conn->query("UPDATE " . $this->table . " SET is_read=1 WHERE user_id=$uid AND is_read=0");
        
        return ["status" => "success", "notifications" => $notifs];
    }

    // --- 3. GET UNREAD COUNT ---
    public function getUnreadCount($userId) {
        $userId = (int)$userId;
        $sql = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE user_id = ? AND is_read=0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return ["status" => "success", "count" => (int)$row['count']];
        }
        return ["status" => "error", "count" => 0];
    }
}
?>