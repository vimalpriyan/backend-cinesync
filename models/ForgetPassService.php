<?php
class ForgetPassService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function initiateReset($data) {
        $email = $this->conn->real_escape_string($data->email);
        $check = $this->conn->query("SELECT user_id FROM users WHERE email='$email'");
        if ($check->num_rows == 0) return ["status" => "success", "message" => "If email exists, link sent."];

        $token = bin2hex(random_bytes(16));
        $hash = password_hash($token, PASSWORD_DEFAULT);
        $exp = date("Y-m-d H:i:s", time() + 3600);
        
        $this->conn->query("DELETE FROM password_resets WHERE email='$email'");
        $this->conn->query("INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$hash', '$exp')");
        
        return ["status" => "success", "message" => "Token Generated", "temp_token" => $token];
    }

    public function resetPassword($data) {
        $token = $data->token;
        $newPass = password_hash($data->new_password, PASSWORD_DEFAULT);
        
        // Find valid token
        $res = $this->conn->query("SELECT * FROM password_resets WHERE expires_at > NOW()");
        while($row = $res->fetch_assoc()) {
            if(password_verify($token, $row['token'])) {
                $email = $row['email'];
                $this->conn->query("UPDATE users SET password='$newPass' WHERE email='$email'");
                $this->conn->query("DELETE FROM password_resets WHERE email='$email'");
                return ["status" => "success", "message" => "Password Changed"];
            }
        }
        return ["status" => "error", "message" => "Invalid Token"];
    }
}
?>