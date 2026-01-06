<?php
require_once __DIR__ . '/../models/AuthService.php';

class AuthController {
    private $conn;

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // Standard Login
    public function login() {
        $data = json_decode(file_get_contents("php://input")) ?? (object) $_POST;
        $service = new AuthService($this->conn);
        echo json_encode($service->login($data));
    }

    // Get Profile
    public function profile() {
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) { 
            echo json_encode(["status" => "error", "message" => "ID required"]); 
            return; 
        }
        $service = new AuthService($this->conn);
        echo json_encode($service->getProfile($userId));
    }

    // Update Details (Refined)
    public function update_details() {
        // 1. Determine Input Source (JSON vs Form Data)
        $rawInput = file_get_contents("php://input");
        $inputData = json_decode($rawInput, true);

        // If JSON is empty, fall back to $_POST (likely Multipart request)
        if (empty($inputData)) {
            $inputData = $_POST;
        }

        // 2. Extract Variables
        $userId   = $inputData['user_id'] ?? null;
        $username = $inputData['username'] ?? null;
        $fullName = $inputData['full_name'] ?? null; 
        $bio      = $inputData['bio'] ?? null;
        $mediaUrl = $inputData['profile_pic_url'] ?? null; // Keep existing URL if no new file

        // 3. Validation
        if (!$userId) {
            echo json_encode(["status" => "error", "message" => "User ID is missing"]);
            return;
        }

        // 4. Handle File Upload (Securely)
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            
            // Security: Allow only specific image types
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExt = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedTypes)) {
                $targetDir = __DIR__ . "/../uploads/";
                
                // Ensure directory exists
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

                // Generate unique name
                $fileName = time() . "_" . uniqid() . "." . $fileExt;
                $targetFilePath = $targetDir . $fileName;

                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
                    // Dynamic Base URL logic
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST']; // e.g., localhost or 10.36.249.194:8012
                    
                    // Construct URL dynamically
                    $mediaUrl = "$protocol://$host/cinesync/uploads/$fileName"; 
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
                    return;
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, GIF allowed."]);
                return;
            }
        }

        // 5. Prepare Data Object for Service
        $data = (object) [
            'user_id'         => $userId,
            'username'        => $username,
            'full_name'       => $fullName,
            'bio'             => $bio,
            'profile_pic_url' => $mediaUrl
        ];
        
        $service = new AuthService($this->conn);
        echo json_encode($service->updateProfile($data));
    }
    
    // Send OTP
    public function send_otp() {
        $payload = json_decode(file_get_contents("php://input")) ?? (object)$_POST;
        $service = new AuthService($this->conn);
        echo json_encode($service->sendOtp($payload));
    }
    
    // Register
    public function register($data = null) {
        $payload = $data ?? json_decode(file_get_contents("php://input"));
        $service = new AuthService($this->conn);
        echo json_encode($service->register($payload));
    }
    // Change Password
    public function change_password() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->changePassword($data));
    }

    // Delete Account
    public function delete_account() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->deleteAccount($data));
    }

    // Update Privacy
    public function update_privacy() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->updatePrivacy($data));
    }

    // Toggle Notifications
    public function toggle_notifications() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->toggleNotifications($data));
    }

    public function toggle_2fa() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->toggle2FA($data));
    }

    public function update_language() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->updateLanguage($data));
    }

    public function update_ads_settings() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->updateAdsSettings($data));
    }

    public function get_login_activity() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new AuthService($this->conn))->getLoginActivity($data));
    }
}
?>