<?php
require_once __DIR__ . '/../models/ReportService.php';

class ReportController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function submit_report() {
        $data = json_decode(file_get_contents("php://input"));
        $service = new ReportService($this->conn);
        echo json_encode($service->createReport($data));
    }
}
?>