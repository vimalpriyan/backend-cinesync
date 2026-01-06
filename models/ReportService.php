<?php
class ReportService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createReport($data) {
        $userId = $data->user_id ?? null;
        $category = $data->category ?? '';
        $description = $data->description ?? '';

        if (!$userId || empty($category) || empty($description)) {
            return ["status" => "error", "message" => "Missing required fields"];
        }

        $stmt = $this->conn->prepare("INSERT INTO reports (user_id, category, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $category, $description);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Report submitted successfully"];
        }
        return ["status" => "error", "message" => "Failed to submit report: " . $this->conn->error];
    }
}
?>