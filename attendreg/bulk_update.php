<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/schoolpro/database/database.php";
$dbo = new Database();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['students']) || !is_array($data['students'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$updated = 0;

foreach ($data['students'] as $student) {
    $student_id = $student['student_id'] ?? '';
    $course_id = $student['course_id'] ?? '';
    $session_id = $student['session_id'] ?? '';
    $on_date = $student['on_date'] ?? '';
    $status = $student['status'] ?? '';

    if ($student_id && $course_id && $session_id && $on_date && $status) {
        $query = "UPDATE attendance_details 
                  SET status = :status, on_date = :on_date 
                  WHERE student_id = :student_id 
                    AND course_id = :course_id 
                    AND session_id = :session_id";

        $stmt = $dbo->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':on_date', $on_date);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();
        $updated++;
    }
}

echo json_encode(["success" => true, "updated" => $updated]);

