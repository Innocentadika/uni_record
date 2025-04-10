<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/schoolpro/database/database.php";

$dbo = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $course_id = $_POST['course_id'] ?? '';
    $session_id = $_POST['session_id'] ?? '';
    $on_date = $_POST['on_date'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($student_id && $course_id && $session_id && $on_date && $status) {
        $query = "UPDATE attendance_details 
                  SET status = :status 
                  WHERE student_id = :student_id 
                    AND course_id = :course_id 
                    AND session_id = :session_id 
                    AND on_date = :on_date";

        $stmt = $dbo->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':on_date', $on_date);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Missing fields."]);
    }
}
