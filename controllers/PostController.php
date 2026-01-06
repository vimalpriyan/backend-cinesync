<?php
require_once __DIR__ . '/../models/PostService.php';

class PostController {
    private $conn;

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // --- Helper: Extract User ID from Authorization Header ---
    private function getViewerId() {
        $headers = null;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Nginx/Apache fallback
            $headers = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        
        // Check for Authorization header (Case insensitive)
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!empty($authHeader) && preg_match('/Bearer\s(\d+)/', $authHeader, $matches)) {
            return (int)$matches[1]; // Return ID as integer
        }
        return 0; // 0 means guest/not logged in
    }

    // --- 1. FETCH POSTS (HOME FEED) ---
    public function getPosts() {
        $viewerId = $this->getViewerId(); 
        $service = new PostService($this->conn);
        echo json_encode($service->getPosts($viewerId));
    }

    // --- 2. FETCH POSTS FOR PROFILE ---
    public function getPostsForUser() {
        $targetUserId = $_GET['user_id'] ?? null; 
        $viewerId = $this->getViewerId();       

        if (!$targetUserId) {
            echo json_encode(["status" => "error", "message" => "Target User ID is required"]);
            return;
        }

        $service = new PostService($this->conn);
        echo json_encode($service->getPostsForUser($targetUserId, $viewerId));
    }

    // --- 3. CREATE POST ---
    public function create() {
        // Check if file upload (Multipart)
        if (isset($_FILES['image']) && isset($_POST['user_id'])) {
            $userId   = $_POST['user_id'];
            $caption  = $_POST['caption'] ?? '';
            $location = $_POST['location'] ?? '';

            $targetDir = __DIR__ . "/../uploads/";
            if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }

            // Validate File
            $allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'mp4'];
            $fileExt = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedTypes)) {
                $fileName = time() . "_" . uniqid() . "." . $fileExt; // Unique name
                $targetFilePath = $targetDir . $fileName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    // Dynamic URL construction
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $mediaUrl = "$protocol://$host/cinesync/uploads/" . $fileName; 

                    $data = (object) [
                        'user_id' => $userId,
                        'media_url' => $mediaUrl,
                        'caption' => $caption,
                        'location' => $location
                    ];

                    $service = new PostService($this->conn);
                    echo json_encode($service->createPost($data));
                } else {
                    echo json_encode(["status" => "error", "message" => "File upload failed to move."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, GIF, MP4 allowed."]);
            }
        } 
        // Handle JSON Input (Text only posts, rarely used but good fallback)
        else {
            $data = json_decode(file_get_contents("php://input"));
            if($data) {
                $service = new PostService($this->conn);
                echo json_encode($service->createPost($data));
            } else {
                 echo json_encode(["status" => "error", "message" => "No data received or invalid format."]);
            }
        }
    }
}
?>