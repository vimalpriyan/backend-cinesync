<?php
require_once __DIR__ . '/../models/ForgetPassService.php';

class ForgetPassController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function initiateReset() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new ForgetPassService($this->conn))->initiateReset($data));
    }
    public function resetPassword() {
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode((new ForgetPassService($this->conn))->resetPassword($data));
    }
}
?>