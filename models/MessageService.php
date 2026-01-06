<?php
class MessageService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. SEND MESSAGE
    public function sendMessage($data) {
        $sid = isset($data->sender_id) ? (int)$data->sender_id : 0;
        $convoId = isset($data->conversation_id) ? (int)$data->conversation_id : 0;
        $rid = isset($data->receiver_id) ? (int)$data->receiver_id : 0;
        $msgRaw = isset($data->content) ? $data->content : (isset($data->message_text) ? $data->message_text : '');
        
        $msg = $this->conn->real_escape_string(trim($msgRaw));

        if(!$sid || (empty($msg) && empty($data->image_url))) {
            return ["status" => "error", "message" => "Missing data"];
        }

        // If no conversation_id, find or create one (Private Chat)
        if (!$convoId && $rid) {
            $convoId = $this->findOrCreatePrivateConversation($sid, $rid);
        }

        if (!$convoId) {
            return ["status" => "error", "message" => "Could not determine conversation"];
        }

        $img = isset($data->image_url) ? "'" . $this->conn->real_escape_string($data->image_url) . "'" : "NULL";
        $audio = isset($data->audio_url) ? "'" . $this->conn->real_escape_string($data->audio_url) . "'" : "NULL";

        $sql = "INSERT INTO message (sender_id, conversation_id, message_text, image_url, audio_url) 
                VALUES ($sid, $convoId, '$msg', $img, $audio)";
                
        if ($this->conn->query($sql)) {
            // Update conversation timestamp locally
            $this->conn->query("UPDATE conversations SET updated_at = NOW() WHERE id = $convoId");
            return ["status" => "success", "message" => "Message Sent", "conversation_id" => $convoId];
        }
        return ["status" => "error", "message" => "Database Error: " . $this->conn->error];
    }

    private function findOrCreatePrivateConversation($u1, $u2) {
        // Check for existing private conversation
        $sql = "SELECT c.id FROM conversations c 
                JOIN conversation_participants p1 ON c.id = p1.conversation_id 
                JOIN conversation_participants p2 ON c.id = p2.conversation_id 
                WHERE c.is_group = 0 AND p1.user_id = $u1 AND p2.user_id = $u2";
        $res = $this->conn->query($sql);
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc()['id'];
        }

        // Create new
        $this->conn->query("INSERT INTO conversations (is_group) VALUES (0)");
        $id = $this->conn->insert_id;
        $this->conn->query("INSERT INTO conversation_participants (conversation_id, user_id) VALUES ($id, $u1), ($id, $u2)");
        return $id;
    }

    // 2. GET MESSAGES
    public function getConversation($data) {
        $convoId = isset($data->conversation_id) ? (int)$data->conversation_id : (isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0);
        
        // Backward compatibility for user-based query
        if (!$convoId) {
            $u1 = isset($data->user1_id) ? (int)$data->user1_id : (isset($_GET['user1_id']) ? (int)$_GET['user1_id'] : 0);
            $u2 = isset($data->user2_id) ? (int)$data->user2_id : (isset($_GET['user2_id']) ? (int)$_GET['user2_id'] : 0);
            if ($u1 && $u2) {
                $convoId = $this->findOrCreatePrivateConversation($u1, $u2);
            }
        }

        if (!$convoId) {
            return ["status" => "error", "message" => "Missing conversation ID", "data" => []];
        }

        $sql = "SELECT m.*, u.username as sender_name, u.profile_pic_url as sender_pic 
                FROM message m
                JOIN users u ON m.sender_id = u.user_id 
                WHERE m.conversation_id = $convoId 
                ORDER BY created_at ASC";
                
        $result = $this->conn->query($sql);
        $msgs = [];
        if($result) {
            while($row = $result->fetch_assoc()) {
                // Fetch Reactions for this message
                $msgId = $row['message_id'];
                $rSql = "SELECT r.reaction_type, r.user_id FROM message_reactions r WHERE r.message_id = $msgId";
                $rRes = $this->conn->query($rSql);
                $reactions = [];
                if ($rRes) {
                    while($rRow = $rRes->fetch_assoc()) {
                        $reactions[] = [
                            "emoji" => $rRow['reaction_type'],
                            "user_id" => (string)$rRow['user_id']
                        ];
                    }
                }

                // Map to DTO format
                $msgs[] = [
                    "id" => (string)$row['message_id'],
                    "conversation_id" => (string)$row['conversation_id'],
                    "sender_id" => (string)$row['sender_id'],
                    "receiver_id" => "", // Not critical in conversation-based
                    "content" => $row['message_text'],
                    "image_url" => $row['image_url'],
                    "audio_url" => $row['audio_url'],
                    "is_read" => (bool)$row['is_read'],
                    "timestamp" => strtotime($row['created_at']) * 1000,
                    "reactions" => $reactions
                ];
            }
        }
        
        // Also fetch conversation details
        $convoInfo = $this->getConversationDetails($convoId);

        // Filter out self for private chats to make header logic easier
        $uid = isset($data->user_id) ? (int)$data->user_id : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);
        if ($uid && $convoInfo && !$convoInfo['is_group']) {
            $convoInfo['participants'] = array_values(array_filter($convoInfo['participants'], function($p) use ($uid) {
                return (int)$p['user_id'] !== $uid;
            }));
        }

        return ["status" => "success", "conversation" => $convoInfo, "messages" => $msgs];
    }

    private function getConversationDetails($id) {
        $res = $this->conn->query("SELECT * FROM conversations WHERE id = $id");
        if (!$res) return null;
        $c = $res->fetch_assoc();
        
        // Participants
        $pres = $this->conn->query("SELECT u.user_id, u.username, u.full_name, u.profile_pic_url FROM conversation_participants cp JOIN users u ON cp.user_id = u.user_id WHERE cp.conversation_id = $id");
        $parts = [];
        while($p = $pres->fetch_assoc()) {
            $parts[] = [
                "user_id" => (string)$p['user_id'],
                "username" => $p['username'],
                "full_name" => $p['full_name'],
                "profile_pic_url" => $p['profile_pic_url']
            ];
        }

        return [
            "id" => (string)$c['id'],
            "group_name" => $c['group_name'],
            "group_photo" => $c['group_photo'],
            "is_group" => (bool)$c['is_group'],
            "participants" => $parts,
            "updated_at" => strtotime($c['updated_at']) * 1000
        ];
    }

    // 3. GET INBOX
    public function getConversations($data) {
        $uid = isset($data->user_id) ? (int)$data->user_id : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);

        if (!$uid) {
             return ["status" => "error", "message" => "Missing User ID", "data" => []];
        }

        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM conversation_favorites WHERE user_id = $uid AND conversation_id = c.id) as is_favorite,
                (SELECT COUNT(*) FROM message WHERE conversation_id = c.id AND is_read = 0 AND sender_id != $uid) as unread_count,
                (SELECT message_id FROM message WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_msg_id,
                (SELECT sender_id FROM message WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_sender_id,
                (SELECT message_text FROM message WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_text,
                (SELECT created_at FROM message WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_time,
                (SELECT is_read FROM message WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_is_read
                FROM conversations c
                JOIN conversation_participants cp ON c.id = cp.conversation_id
                WHERE cp.user_id = $uid
                ORDER BY updated_at DESC";

        $result = $this->conn->query($sql);
        $convos = [];
        if($result) {
            while($c = $result->fetch_assoc()) {
                $details = $this->getConversationDetails($c['id']);
                $details['is_favorite'] = (bool)$c['is_favorite'];
                $details['unread_count'] = (int)$c['unread_count'];
                
                // For private chats, filter out self from participants to show "other" person
                if (!$details['is_group']) {
                    $details['participants'] = array_values(array_filter($details['participants'], function($p) use ($uid) {
                        return (int)$p['user_id'] !== $uid;
                    }));
                }

                $details['last_message'] = [
                    "id" => (string)($c['last_msg_id'] ?? ""),
                    "conversation_id" => (string)$c['id'],
                    "sender_id" => (string)($c['last_sender_id'] ?? ""),
                    "receiver_id" => "",
                    "content" => $c['last_text'] ?? "",
                    "timestamp" => $c['last_time'] ? strtotime($c['last_time']) * 1000 : 0,
                    "is_read" => (bool)($c['last_is_read'] ?? 0),
                    "reactions" => []
                ];
                $convos[] = $details;
            }
        }
        return ["status" => "success", "conversations" => $convos];
    }

    public function toggleFavorite($data) {
        $convoId = isset($data->conversation_id) ? (int)$data->conversation_id : 0;
        $uid = isset($data->user_id) ? (int)$data->user_id : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);

        if (!$convoId || !$uid) {
            return ["status" => "error", "message" => "Missing data"];
        }

        // Check if exists
        $check = $this->conn->query("SELECT id FROM conversation_favorites WHERE user_id = $uid AND conversation_id = $convoId");
        
        if ($check && $check->num_rows > 0) {
            // Remove
            $sql = "DELETE FROM conversation_favorites WHERE user_id = $uid AND conversation_id = $convoId";
            $msg = "Removed from Favorites";
        } else {
            // Add
            $sql = "INSERT INTO conversation_favorites (user_id, conversation_id) VALUES ($uid, $convoId)";
            $msg = "Added to Favorites";
        }
        
        if ($this->conn->query($sql)) {
            return ["status" => "success", "message" => $msg];
        }
        return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
    }

    // 4. ADD REACTION
    public function addReaction($data) {
        $msgId = isset($data->message_id) ? (int)$data->message_id : 0;
        $uid = isset($data->user_id) ? (int)$data->user_id : 0;
        $reaction = isset($data->reaction_type) ? $this->conn->real_escape_string($data->reaction_type) : '';

        if (!$msgId || !$uid || empty($reaction)) {
            return ["status" => "error", "message" => "Missing data"];
        }

        // Check if reaction exists
        $checkSql = "SELECT id, reaction_type FROM message_reactions WHERE message_id = $msgId AND user_id = $uid";
        $result = $this->conn->query($checkSql);

        if ($result && $result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['reaction_type'] === $reaction) {
                // TOGGLE: Remove if same reaction
                $delSql = "DELETE FROM message_reactions WHERE id = " . $existing['id'];
                if ($this->conn->query($delSql)) {
                    return ["status" => "success", "message" => "Reaction Removed", "action" => "removed"];
                }
            } else {
                // REPLACE: Update if different reaction
                $updSql = "UPDATE message_reactions SET reaction_type = '$reaction', created_at = NOW() WHERE id = " . $existing['id'];
                if ($this->conn->query($updSql)) {
                    return ["status" => "success", "message" => "Reaction Updated", "action" => "updated"];
                }
            }
        } else {
            // INSERT: New reaction
            $insSql = "INSERT INTO message_reactions (message_id, user_id, reaction_type) VALUES ($msgId, $uid, '$reaction')";
            if ($this->conn->query($insSql)) {
                return ["status" => "success", "message" => "Reaction Added", "action" => "added"];
            }
        }

        return ["status" => "error", "message" => "Database Error: " . $this->conn->error];
    }
}
?>