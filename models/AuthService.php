<?php
require_once __DIR__ . '/../api/models/MailService.php';

class AuthService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- LOGIN ---
    public function login($data) {
        $email = is_object($data) ? ($data->email ?? '') : ($data['email'] ?? '');
        $password = is_object($data) ? ($data->password ?? '') : ($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            return ["status" => "error", "message" => "Email and password required"];
        }

        // Use Prepared Statement
        $stmt = $this->conn->prepare("SELECT user_id AS id, username, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Log Login Activity
                $userId = $user['id'];
                // Basic detection for now - in production use User-Agent headers
                $deviceName = "Android Device"; 
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                
                // Check if device exists for user to update timestamp, or insert new
                // For simplicity, just insert new record for every login or update generic 'Current Session'
                // Let's Insert a new record
                $logStmt = $this->conn->prepare("INSERT INTO login_activities (user_id, device_name, ip_address) VALUES (?, ?, ?)");
                $logStmt->bind_param("iss", $userId, $deviceName, $ip);
                $logStmt->execute();

                // Success
                return [
                    "status" => "success", 
                    "message" => "Login Successful", 
                    "user_id" => $user['id'], 
                    "username" => $user['username'],
                    "role" => $user['role']
                ];
            }
            return ["status" => "error", "message" => "Invalid credentials"];
        } else {
            return ["status" => "error", "message" => "User not found"];
        }
    }

    // --- GET PROFILE ---
    public function getProfile($userId) {
        $stmt = $this->conn->prepare("SELECT user_id, username, email, full_name, bio, profile_pic_url, role, is_verified, created_at FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Ensure types are friendly for JSON (Frontend often prefers Strings for IDs)
            $user['user_id'] = (string)$user['user_id']; 
            $user['is_verified'] = (string)$user['is_verified']; 
            return ["status" => "success", "user" => $user];
        } else {
            return ["status" => "error", "message" => "User not found"];
        }
    }

    // --- UPDATE PROFILE (Optimized) ---
    public function updateProfile($data) {
        $userId = $data->user_id ?? null;
        if (!$userId) return ["status" => "error", "message" => "User ID is missing"];

        // Check if user exists first
        if (!$this->userExists($userId)) {
            return ["status" => "error", "message" => "User does not exist"];
        }

        $updates = [];
        $params = [];
        $types = "";

        // Dynamically build query
        if (!empty($data->full_name)) { $updates[] = "full_name = ?"; $params[] = $data->full_name; $types .= "s"; }
        if (!empty($data->username))  { $updates[] = "username = ?";  $params[] = $data->username;  $types .= "s"; }
        if (isset($data->bio))        { $updates[] = "bio = ?";       $params[] = $data->bio;       $types .= "s"; } // Allow empty string for bio
        if (!empty($data->profile_pic_url)) { $updates[] = "profile_pic_url = ?"; $params[] = $data->profile_pic_url; $types .= "s"; }
        // Added Email and Mobile support
        if (!empty($data->email))     { $updates[] = "email = ?";     $params[] = $data->email;     $types .= "s"; }
        if (!empty($data->mobile))    { $updates[] = "mobile = ?";    $params[] = $data->mobile;    $types .= "s"; }

        if (empty($updates)) return ["status" => "success", "message" => "No changes detected"];

        // Construct SQL
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
        $params[] = $userId;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
             // Return the FRESH profile data immediately
             // This allows the App to update the screen without a second API call
             return [
                 "status" => "success", 
                 "message" => "Profile updated successfully",
                 "data" => $this->getProfile($userId) 
             ];
        } else {
             return ["status" => "error", "message" => "Database update failed: " . $stmt->error];
        }
    }

    // --- SEND OTP ---
    public function sendOtp($data) {
        $email = is_object($data) ? ($data->email ?? '') : ($data['email'] ?? '');
        if (empty($email)) return ["status" => "error", "message" => "Email is required"];

        // 1. Check if email exists in users table (Prepared)
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            return ["status" => "error", "message" => "Email already registered"];
        }

        // 2. Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);

        // 3. Delete old OTPs for this email
        $delStmt = $this->conn->prepare("DELETE FROM registration_otps WHERE email = ?");
        $delStmt->bind_param("s", $email);
        $delStmt->execute();

        // 4. Insert new OTP
        $insStmt = $this->conn->prepare("INSERT INTO registration_otps (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $insStmt->bind_param("ss", $email, $hashedOtp);

        if ($insStmt->execute()) {
            $mailService = new MailService();
            if ($mailService->sendOtp($email, $otp)) {
                return ["status" => "success", "message" => "OTP sent to email"];
            } else {
                return ["status" => "error", "message" => "Failed to send email. Check SMTP settings."];
            }
        }
        return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
    }

    // --- REGISTER USER ---
    public function register($data) {
        if (is_array($data)) $data = (object) $data;

        // Validation
        $required = ['username', 'email', 'mobile', 'password', 'role', 'otp'];
        foreach($required as $field) {
            if (empty($data->$field)) return ["status" => "error", "message" => "Missing field: $field"];
        }

        $email = $data->email;
        $otpInput = trim($data->otp);

        // 1. Verify OTP (Prepared)
        $stmt = $this->conn->prepare("SELECT otp_code FROM registration_otps WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) return ["status" => "error", "message" => "OTP expired or invalid"];
        
        $otpData = $result->fetch_assoc();
        if (!password_verify($otpInput, $otpData['otp_code'])) {
            return ["status" => "error", "message" => "Wrong OTP"];
        }

        // 2. Prepare User Data
        $username = trim($data->username);
        $mobile = trim($data->mobile);
        $role = trim($data->role);
        $passHash = password_hash(trim($data->password), PASSWORD_DEFAULT);
        
        // Handle Full Name Fallback
        // If controller sent full_name, use it. Otherwise use username.
        $fullName = !empty($data->full_name) ? trim($data->full_name) : $username; 

        // 3. Insert User (Prepared)
        $sql = "INSERT INTO users (full_name, username, email, mobile, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $this->conn->prepare($sql);
        $insertStmt->bind_param("ssssss", $fullName, $username, $email, $mobile, $passHash, $role);

        if ($insertStmt->execute()) {
            // Clean up OTP
            $cleanStmt = $this->conn->prepare("DELETE FROM registration_otps WHERE email = ?");
            $cleanStmt->bind_param("s", $email);
            $cleanStmt->execute();

            return ["status" => "success", "message" => "Registration Successful"];
        }
        
        return ["status" => "error", "message" => "Registration failed: " . $this->conn->error];
    }

    // Helper to check if user exists
    private function userExists($id) {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // --- CHANGE PASSWORD ---
    public function changePassword($data) {
        $userId = $data->user_id ?? null;
        $oldPass = $data->old_password ?? '';
        $newPass = $data->new_password ?? '';

        if (!$userId || empty($oldPass) || empty($newPass)) {
            return ["status" => "error", "message" => "Missing fields"];
        }

        // Verify Old Password
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if (password_verify($oldPass, $row['password'])) {
                // Update
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $upd = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $upd->bind_param("si", $newHash, $userId);
                
                if ($upd->execute()) {
                    return ["status" => "success", "message" => "Password changed successfully"];
                }
            } else {
                return ["status" => "error", "message" => "Incorrect old password"];
            }
        }
        return ["status" => "error", "message" => "User not found"];
    }

    // --- DELETE ACCOUNT ---
    public function deleteAccount($data) {
        $userId = $data->user_id ?? null;
        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        // Cascade delete handled by DB constraints usually, but explicit check implies intention
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Account deleted"];
        }
        return ["status" => "error", "message" => "Delete failed: " . $this->conn->error];
    }

    // --- PRIVACY SETTINGS ---
    public function updatePrivacy($data) {
        $userId = $data->user_id ?? null;
        $isPrivate = isset($data->is_private) ? (int)$data->is_private : 0; // 1 or 0

        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("UPDATE users SET is_private = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $isPrivate, $userId);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Privacy updated", "is_private" => (bool)$isPrivate];
        }
        return ["status" => "error", "message" => "DB Error"];
    }

    // --- NOTIFICATION SETTINGS ---
    public function toggleNotifications($data) {
        $userId = $data->user_id ?? null;
        $enabled = isset($data->notifications_enabled) ? (int)$data->notifications_enabled : 1;

        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("UPDATE users SET notifications_enabled = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $enabled, $userId);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Notifications updated", "notifications_enabled" => (bool)$enabled];
        }
        return ["status" => "error", "message" => "DB Error"];
    }

    // --- 2FA SETTINGS ---
    public function toggle2FA($data) {
        $userId = $data->user_id ?? null;
        $enabled = isset($data->two_factor_enabled) ? (int)$data->two_factor_enabled : 0;

        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("UPDATE users SET two_factor_enabled = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $enabled, $userId);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "2FA updated", "two_factor_enabled" => (bool)$enabled];
        }
        return ["status" => "error", "message" => "DB Error"];
    }

    // --- LANGUAGE SETTINGS ---
    public function updateLanguage($data) {
        $userId = $data->user_id ?? null;
        $language = $data->language ?? 'English';

        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("UPDATE users SET language = ? WHERE user_id = ?");
        $stmt->bind_param("si", $language, $userId);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Language updated", "language" => $language];
        }
        return ["status" => "error", "message" => "DB Error"];
    }

    // --- AD SETTINGS ---
    public function updateAdsSettings($data) {
        $userId = $data->user_id ?? null;
        $personalized = isset($data->personalized_ads) ? (int)$data->personalized_ads : 1;
        $partners = isset($data->ad_partners) ? (int)$data->ad_partners : 1;
        $sharing = isset($data->data_sharing) ? (int)$data->data_sharing : 0;

        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("UPDATE users SET personalized_ads = ?, ad_partners = ?, data_sharing = ? WHERE user_id = ?");
        $stmt->bind_param("iiii", $personalized, $partners, $sharing, $userId);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Ad settings updated"];
        }
        return ["status" => "error", "message" => "DB Error"];
    }

    // --- LOGIN ACTIVITY ---
    public function getLoginActivity($data) {
        $userId = $data->user_id ?? null;
        if (!$userId) return ["status" => "error", "message" => "User ID required"];

        $stmt = $this->conn->prepare("SELECT device_name, location, last_active, ip_address FROM login_activities WHERE user_id = ? ORDER BY last_active DESC LIMIT 10");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        return ["status" => "success", "activities" => $activities];
    }
}
?>