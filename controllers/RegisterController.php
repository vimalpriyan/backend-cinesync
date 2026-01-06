<?php
require_once __DIR__ . '/../models/RegisterService.php';

class RegisterController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function sendOtp() {
        echo json_encode((new RegisterService($this->conn))->sendRegistrationOtp(json_decode(file_get_contents("php://input"))));
    }

    public function register() {
        echo json_encode((new RegisterService($this->conn))->registerUser(json_decode(file_get_contents("php://input"))));
    }
}
?>