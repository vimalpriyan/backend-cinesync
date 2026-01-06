<?php

class CommentService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new comment on a post
     */
    public function createComment($data) {
        // 1. Validate required fields
        if (empty($data->user_id) || empty($data->post_id) || empty($data->content)) {
            return [
                "status" => "error", 
                "message" => "Missing required fields: user_id, post_id, or content"
            ];
        }

        // 2. Sanitize inputs
        $uid = (int)$data->user_id;
        $pid = (int)$data->post_id;
        $commentText = trim($data->content);

        // 3. Prepare SQL statement using your column name: comment_text
        $sql = "INSERT INTO comments (user_id, post_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return ["status" => "error", "message" => "SQL Prepare failed: " . $this->conn->error];
        }

        $stmt->bind_param("iis", $uid, $pid, $commentText);

        // 4. Execute and Return
        if ($stmt->execute()) {
            $successResponse = [
                "status" => "success", 
                "message" => "Comment added successfully",
                "comment_id" => (string)$this->conn->insert_id
            ];
            $this->notifyPostOwner($pid, $uid);
            return $successResponse;
        } else {
            return ["status" => "error", "message" => "Execution failed: " . $stmt->error];
        }
    }

    private function notifyPostOwner($postId, $commenterId) {
        $query = $this->conn->query("SELECT user_id FROM posts WHERE post_id=$postId");
        if ($query && $query->num_rows > 0) {
            $row = $query->fetch_assoc();
            $ownerId = $row['user_id'];
            if ((int)$ownerId !== (int)$commenterId) {
                require_once __DIR__ . '/NotificationService.php';
                $notifService = new NotificationService($this->conn);
                $notifService->create($ownerId, 'comment', $postId, $commenterId);
            }
        }
    }

    /**
     * Fetch all comments for a specific post with User details
     */
    public function getComments($postId) {
        if (empty($postId)) {
            return ["status" => "error", "message" => "Post ID is required"];
        }

        $pid = (int)$postId;
        
        /* Using 'comment_text AS content' so the Android app receives the field 
           it expects without changing Kotlin DTOs.
        */
        $sql = "SELECT 
                    c.comment_id, 
                    c.user_id, 
                    c.comment_text AS content, 
                    c.created_at, 
                    u.username, 
                    u.profile_pic_url 
                FROM comments c 
                INNER JOIN users u ON c.user_id = u.user_id 
                WHERE c.post_id = ? 
                ORDER BY c.created_at ASC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return ["status" => "error", "message" => "SQL Prepare failed: " . $this->conn->error];
        }

        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            // Casting IDs to strings for Android compatibility
            $row['comment_id'] = (string)$row['comment_id'];
            $row['user_id'] = (string)$row['user_id'];
            
            if (empty($row['profile_pic_url'])) {
                $row['profile_pic_url'] = ""; 
            }

            $comments[] = $row;
        }

        return [
            "status" => "success", 
            "comments" => $comments
        ];
    }
}
?>