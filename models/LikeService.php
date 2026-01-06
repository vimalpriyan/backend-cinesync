<?php
require_once __DIR__ . '/NotificationService.php';

class LikeService {
    private $conn;
    private $notifService;

    public function __construct($db) {
        $this->conn = $db;
        $this->notifService = new NotificationService($db);
    }

    public function toggleLike($data) {
        if (empty($data->user_id) || empty($data->post_id)) {
            return ["status" => "error", "message" => "Missing User ID or Post ID"];
        }
        $uid = (int)$data->user_id;    // The person Liking
        $pid = (int)$data->post_id;    // The Post being liked

        // Check if already liked
        $check = $this->conn->query("SELECT like_id FROM likes WHERE user_id=$uid AND post_id=$pid");
        
        if ($check && $check->num_rows > 0) {
            // UNLIKE
            $this->conn->query("DELETE FROM likes WHERE user_id=$uid AND post_id=$pid");
            // Optional: Remove the notification if they unlike? 
            // $this->conn->query("DELETE FROM notification WHERE sender_id=$uid AND type='like' AND reference_id=$pid");
            
            return ["status" => "success", "action" => "unliked", "message" => "Post unliked"];
        } else {
            // LIKE
            $this->conn->query("INSERT INTO likes (user_id, post_id) VALUES ($uid, $pid)");

            // --- SEND NOTIFICATION ---
            // 1. Find who owns the post
            $postOwnerQuery = $this->conn->query("SELECT user_id FROM posts WHERE post_id=$pid");
            if ($postOwnerQuery && $postOwnerQuery->num_rows > 0) {
                $row = $postOwnerQuery->fetch_assoc();
                $postOwnerId = $row['user_id'];

                // 2. Only notify if I am not liking my own post
                if ((int)$postOwnerId !== (int)$uid) {
                    // Receiver = PostOwner, Type = 'like', Ref = PostID, Sender = Me ($uid)
                    $this->notifService->create($postOwnerId, 'like', $pid, $uid); 
                }
            }
            // -------------------------

            return ["status" => "success", "action" => "liked", "message" => "Post liked"];
        }
    }
}
?>