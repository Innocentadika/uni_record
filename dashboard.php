<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();

// Make sure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch sessions
$query_sessions = "SELECT DISTINCT session_id, semester, year FROM session_details";
$stmt_sessions = $dbo->conn->prepare($query_sessions);
$stmt_sessions->execute();
$sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique course titles registered by the student/faculty
$query_courses = "SELECT DISTINCT course_id, current_course 
                  FROM course_registration 
                  WHERE student_id = :student_id";
$stmt_courses = $dbo->conn->prepare($query_courses);
$stmt_courses->bindParam(':student_id', $student_id);
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Portal</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
    <div class="page">
        <div class="header-area">
            <div class="logo-area"><h2 class="logo">ATTENDANCE APP</h2></div>
            <div class="logout-area"><button id="btnLogout" class="btnlogout">LOGOUT</button></div>
        </div>

        <div class="session-area">
            <label>SESSION:</label>
            <select id="ddlclass">
                <option value="">SELECT ONE</option>
                <?php foreach ($sessions as $session): ?>
                    <option value="<?php echo htmlspecialchars($session['session_id']); ?>">
                        <?php echo htmlspecialchars($session['year']) . " - Semester " . htmlspecialchars($session['semester']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="classlist-area" id="classlistarea">
            <?php foreach ($courses as $course): ?>
                <div class="classcard" data-course-id="<?php echo htmlspecialchars($course['course_id']); ?>">
                    <?php echo htmlspecialchars($course['course_id']) . " - " . htmlspecialchars($course['current_course']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="classdetails-area" id="classdetailsarea">
            <div class="classdetails">
                <div class="code-area" id="courseCode">Select a course</div>
                <div class="title-area" id="courseTitle">Course title</div>
                <div class="ondate-area">
                    <input type="date" id="attendanceDate">
                </div>
                <button id="submitAttendance">Submit Attendance</button>
            </div>
        </div>

        <div class="studentlist-area" id="studentlistarea">
            <label>STUDENT LIST</label>
            <div class="studentdetails" id="studentDetails"></div>
        </div>
    </div>

    <input type="hidden" id="hiddenFacId" value="<?php echo htmlspecialchars($student_id); ?>">
    <input type="hidden" id="hiddenSelectedCourseID" value="-1">

    <script src="js/jquery.js"></script>
    <script src="js/attendance.js"></script>
</body>
</html>
