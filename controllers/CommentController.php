<?php
// 1. Define the absolute path to the service
$servicePath = __DIR__ . '/../models/CommentService.php';

// 2. Check if the file actually exists before trying to load it
if (!file_exists($servicePath)) {
    die(json_encode([
        "status" => "error", 
        "message" => "Critical Error: Service file missing at $servicePath"
    ]));
}

require_once $servicePath;

class CommentController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"));
        $service = new CommentService($this->conn);
        echo json_encode($service->createComment($data));
    }

    public function getComments() {
        $postId = $_GET['post_id'] ?? null;
        
        if (!$postId) {
            echo json_encode(["status" => "error", "message" => "Post ID is required"]);
            return;
        }

        // This is where line 21 was previously crashing
        $service = new CommentService($this->conn);
        echo json_encode($service->getComments($postId));
    }
}
?>