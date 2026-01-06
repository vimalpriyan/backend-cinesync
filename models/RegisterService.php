<?php
class RegisterService {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    // SEND OTP
    public function sendRegistrationOtp($data) {
        if (empty($data->email)) return ["status" => "error", "message" => "Email is required"];
        $email = $this->conn->real_escape_string(trim($data->email));

        // Check if user already exists
        $check = $this->conn->query("SELECT user_id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) return ["status" => "error", "message" => "Email already registered"];

        // Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
        $expires = date("Y-m-d H:i:s", time() + 600); // 10 mins expiry

        // Save OTP
        $this->conn->query("DELETE FROM registration_otps WHERE email='$email'");
        $sql = "INSERT INTO registration_otps (email, otp_code, expires_at) VALUES ('$email', '$hashedOtp', '$expires')";
        
        if ($this->conn->query($sql)) {
            // In real app, send email here. For now, return OTP for testing.
            return ["status" => "success", "message" => "OTP Sent", "temp_otp_testing" => $otp];
        }
        return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
    }

    // REGISTER USER
    public function registerUser($data) {
        // Validate Fields
        $required = ['full_name', 'username', 'email', 'mobile', 'password', 'role', 'otp'];
        foreach($required as $field) {
            if (empty($data->$field)) return ["status" => "error", "message" => "Missing field: $field"];
        }

        $email = $this->conn->real_escape_string(trim($data->email));
        $otp = trim($data->otp);

        // Verify OTP
        $check = $this->conn->query("SELECT * FROM registration_otps WHERE email='$email' AND expires_at > NOW()");
        if ($check->num_rows === 0) return ["status" => "error", "message" => "OTP expired or invalid"];
        $otpData = $check->fetch_assoc();
        if (!password_verify($otp, $otpData['otp_code'])) return ["status" => "error", "message" => "Wrong OTP"];

        // Create User
        $username = $this->conn->real_escape_string(trim($data->username));
        $fullName = $this->conn->real_escape_string(trim($data->full_name));
        $mobile = $this->conn->real_escape_string(trim($data->mobile));
        $role = $this->conn->real_escape_string(trim($data->role));
        $passHash = password_hash(trim($data->password), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (full_name, username, email, mobile, password, role) 
                VALUES ('$fullName', '$username', '$email', '$mobile', '$passHash', '$role')";

        if ($this->conn->query($sql)) {
            $this->conn->query("DELETE FROM registration_otps WHERE email='$email'");
            return ["status" => "success", "message" => "Registration Successful"];
        }
        return ["status" => "error", "message" => "DB Error: " . $this->conn->error];
    }
}
?>