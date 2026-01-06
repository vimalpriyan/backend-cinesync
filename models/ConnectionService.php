<?php
class ConnectionService {
    private $conn;
    private $table = "connections";

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 1. SEND FRIEND REQUEST ---
    public function sendRequest($senderId, $receiverId) {
        if ($senderId == $receiverId) return ["status" => "error", "message" => "Cannot friend self"];

        // Check if ANY connection exists
        $sql = "SELECT id, status FROM " . $this->table . " 
                WHERE (user_id_1 = ? AND user_id_2 = ?) 
                   OR (user_id_1 = ? AND user_id_2 = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'pending') return ["status" => "error", "message" => "Request already pending"];
            if ($row['status'] === 'accepted') return ["status" => "error", "message" => "Already connected"];
        }

        // Insert new request
        $insertSql = "INSERT INTO " . $this->table . " (user_id_1, user_id_2, status) VALUES (?, ?, 'pending')";
        $stmtIns = $this->conn->prepare($insertSql);
        $stmtIns->bind_param("ii", $senderId, $receiverId);

        if ($stmtIns->execute()) {
            // [FIXED] Table Name: notification (singular)
            $this->createNotification($receiverId, $senderId, 'request', $senderId);
            return ["status" => "success", "message" => "Request sent successfully"];
        }
        return ["status" => "error", "message" => "Database error"];
    }

    // --- 2. RESPOND TO REQUEST (Accept/Reject) ---
    public function respondToRequest($requesterId, $receiverId, $status) {
        if (!in_array($status, ['accepted', 'rejected'])) {
            return ["status" => "error", "message" => "Invalid status"];
        }

        // 1. Update/Delete Connection Table
        if ($status === 'rejected') {
            $sql = "DELETE FROM " . $this->table . " 
                    WHERE (user_id_1 = ? AND user_id_2 = ?) 
                       OR (user_id_1 = ? AND user_id_2 = ?)";
        } else {
            $sql = "UPDATE " . $this->table . " SET status = 'accepted' 
                    WHERE (user_id_1 = ? AND user_id_2 = ?) 
                       OR (user_id_1 = ? AND user_id_2 = ?)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $requesterId, $receiverId, $receiverId, $requesterId);

        if ($stmt->execute()) {
            
            // --- [CRITICAL FIX HERE] ---
            // Changed 'notifications' to 'notification' (Singular)
            $delSql = "DELETE FROM notification 
                       WHERE user_id = ? 
                         AND sender_id = ? 
                         AND type = 'request'";
            
            $delStmt = $this->conn->prepare($delSql);
            $rStr = (string)$receiverId; 
            $sStr = (string)$requesterId;
            $delStmt->bind_param("ss", $rStr, $sStr);
            $delStmt->execute();
            // ---------------------------

            // 2. If Accepted, notify the Requester (Use singular table)
            if ($status === 'accepted') {
                $this->createNotification($requesterId, $receiverId, 'accept', $receiverId);
            }

            return ["status" => "success", "message" => "Request " . $status];
        }
        return ["status" => "error", "message" => "Database error or Request not found"];
    }

    // --- Helper: Create Notification (Table: notification) ---
    private function createNotification($targetUserId, $senderId, $type, $refId) {
        // Prevent duplicates
        if ($type === 'accept') {
            // [FIXED] Table Name: notification (singular)
            $check = $this->conn->prepare("SELECT notification_id FROM notification WHERE user_id=? AND sender_id=? AND type='accept'");
            $check->bind_param("ii", $targetUserId, $senderId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) return; 
        }

        // [FIXED] Table Name: notification (singular)
        $sql = "INSERT INTO notification (user_id, sender_id, type, reference_id, is_read, created_at) 
                VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisi", $targetUserId, $senderId, $type, $refId);
        $stmt->execute();
    }

    // --- 3. GET PENDING REQUESTS ---
    public function getPendingRequests($userId) {
        $sql = "SELECT c.id, c.user_id_1, c.user_id_2, c.status FROM connections c WHERE c.user_id_2 = ? AND c.status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return ["status" => "success", "requests" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
    }

    // --- 4. CHECK STATUS ---
    public function checkStatus($myId, $targetId) {
        $sql = "SELECT status, user_id_1 FROM " . $this->table . " 
                WHERE (user_id_1 = ? AND user_id_2 = ?) 
                   OR (user_id_1 = ? AND user_id_2 = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $myId, $targetId, $targetId, $myId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'accepted') return ["status" => "connected"];
            if ($row['status'] === 'pending') {
                 return ($row['user_id_1'] == $myId) ? ["status" => "sent"] : ["status" => "received"];
            }
        }
        return ["status" => "none"];
    }
    
    public function acceptRequest($rid, $uid) { /* Unused */ }
    // --- 5. DELETE CONNECTION (Un-connect) ---
    public function deleteConnection($user1, $user2) {
        // Delete the connection regardless of status (pending or accepted)
        $sql = "DELETE FROM " . $this->table . " 
                WHERE (user_id_1 = ? AND user_id_2 = ?) 
                   OR (user_id_1 = ? AND user_id_2 = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        
        if ($stmt->execute()) {
             // Also remove any notifications related to requests between these two
             $delNotif = "DELETE FROM notification 
                          WHERE (user_id = ? AND sender_id = ?) 
                             OR (user_id = ? AND sender_id = ?)";
             $stmtNotif = $this->conn->prepare($delNotif);
             $stmtNotif->bind_param("iiii", $user1, $user2, $user2, $user1);
             $stmtNotif->execute();
             
             return ["status" => "success", "message" => "Connection removed"];
        }
        return ["status" => "error", "message" => "Database error"];
    }
}
?>