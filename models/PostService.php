<?php

class PostService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 1. FETCH ALL POSTS (HOME FEED) ---
    public function getPosts($viewerId = 0) {
        return $this->fetchPostsQuery(null, $viewerId);
    }

    // --- 2. FETCH POSTS FOR A SPECIFIC USER ---
    public function getPostsForUser($targetUserId, $viewerId = 0) {
        return $this->fetchPostsQuery($targetUserId, $viewerId);
    }

    // --- PRIVATE HELPER: SHARED QUERY LOGIC ---
    private function fetchPostsQuery($targetUserId = null, $viewerId = 0) {
        $params = [];
        $types = "";

        $sql = "SELECT 
                    p.post_id AS id, 
                    p.user_id, 
                    p.caption, 
                    p.media_url, 
                    p.video_url, 
                    p.location, 
                    p.created_at, 
                    u.username, 
                    u.full_name, 
                    u.profile_pic_url, 
                    u.role AS user_role,
                    (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS likes_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS comments_count,
                    (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id AND user_id = ?) AS is_liked,
                    
                    c.status AS raw_conn_status,
                    c.user_id_1 AS conn_sender_id

                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN connections c ON 
                    (c.user_id_1 = ? AND c.user_id_2 = p.user_id) 
                    OR 
                    (c.user_id_1 = p.user_id AND c.user_id_2 = ?)
                ";

        $params[] = $viewerId;
        $params[] = $viewerId;
        $params[] = $viewerId;
        $types .= "iii";

        if ($targetUserId) {
            $sql .= " WHERE p.user_id = ? ";
            $params[] = $targetUserId;
            $types .= "i";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            // --- [FIX START] FORCE STRING TYPES FOR ANDROID ---
            $row['id'] = (string)$row['id'];
            $row['user_id'] = (string)$row['user_id'];
            $row['likes_count'] = (string)$row['likes_count'];
            $row['comments_count'] = (string)$row['comments_count'];
            // --------------------------------------------------

            $row['is_liked'] = ($row['is_liked'] > 0);

            // Determine Connection Status
            if ($row['user_id'] == $viewerId) {
                $status = 'self';
            } elseif ($row['raw_conn_status'] === 'accepted') {
                $status = 'connected';
            } elseif ($row['raw_conn_status'] === 'pending') {
                $status = ($row['conn_sender_id'] == $viewerId) ? 'sent' : 'received';
            } else {
                $status = 'none';
            }

            $row['connection_status'] = $status;
            
            unset($row['raw_conn_status']);
            unset($row['conn_sender_id']);

            $posts[] = $row;
        }

        return ["status" => "success", "posts" => $posts];
    }

    // --- 3. CREATE POST ---
    public function createPost($data) {
        if (empty($data->user_id) || empty($data->media_url)) {
            return ["status" => "error", "message" => "Missing required fields."];
        }

        $sql = "INSERT INTO posts (user_id, media_url, caption, location) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        $caption  = $data->caption ?? '';
        $location = $data->location ?? '';

        $stmt->bind_param("isss", $data->user_id, $data->media_url, $caption, $location);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Post created successfully"];
        } else {
            return ["status" => "error", "message" => "DB Error: " . $stmt->error];
        }
    }
}
?>