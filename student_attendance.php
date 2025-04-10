<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Get current user ID
$student_id = $_SESSION['student_id'];

// Fetch current user details
$current_user = null;
$stmt_user = $dbo->conn->prepare("SELECT name, student_id FROM faculty_details WHERE student_id = :student_id");
$stmt_user->bindParam(':student_id', $student_id);
$stmt_user->execute();
$current_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Fetch all attendance records for the logged-in student
$query_sessions = "SELECT DISTINCT student_id, session_id, course_id, status, on_date 
                   FROM attendance_details 
                   WHERE student_id = :student_id"; // Filter by student_id
$stmt_sessions = $dbo->conn->prepare($query_sessions);
$stmt_sessions->bindParam(':student_id', $student_id);
$stmt_sessions->execute();
$sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique courses for the student
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
    <title>Attendance Portal</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="css/navb.css">
</head>
<body>

<div>
<div class="nvb">
<nav class="navbar">
    <div class="logo">MySite</div>
    <ul class="nav-links">
      <li><a href="./index.html">Home</a></li>
      <li><a href="./stu_dashboard.php">Dashboard</a></li>
      <li><a href="./student_attendance.php">Attendance</a></li>
      <li><a href="#">Contact</a></li>
      <li><a id="btnLogout">Logout</a></li>
    </ul>
    <div class="burger">
      <div class="line1"></div>
      <div class="line2"></div>
      <div class="line3"></div>
    </div>
</nav>
</div>
</div>

    <div class="page">
        <!-- Header -->
        <div class="header-area">
            <div class="logo-area"><h2 class="logo">MY ATTENDANCE</h2></div>
            <div class="logout-area"><button id="btnLogout" class="btnlogout">LOGOUT</button></div>
        </div>

        <!-- Display logged-in user -->
        <div class="user-area">
            <?php if ($current_user): ?>
                <p class="user-welcome">Welcome, <strong><?php echo htmlspecialchars($current_user['name']); ?></strong> (<?php echo htmlspecialchars($current_user['student_id']); ?>)</p>
            <?php endif; ?>
        </div>
        
        <!-- Session Selector -->
        <div class="session-area">
            <label>UNIT CODE:</label>
            <select id="ddlclass">
                <option value="">MY UNIT</option>
                <?php
                $seen_courses = [];
                foreach ($sessions as $session):
                    if (!in_array($session['course_id'], $seen_courses)):
                        $seen_courses[] = $session['course_id'];
                ?>
                    <option value="<?php echo htmlspecialchars($session['course_id']); ?>">
                        <?php echo htmlspecialchars($session['course_id']); ?>
                    </option>
                <?php
                    endif;
                endforeach;
                ?>
            </select>
        </div>

        <!-- Course List -->
        <div class="classlist-area" id="classlistarea">
            <?php foreach ($courses as $course): ?>
                <div class="classcard" data-course-id="<?php echo htmlspecialchars($course['course_id']); ?>">
                    <?php echo htmlspecialchars($course['course_id']) . " - " . htmlspecialchars($course['current_course']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Class Details -->
        <div class="classdetails-area" id="classdetailsarea">
            <div class="classdetails">
                <div class="code-area" id="courseCode">Select a course</div>
                <div class="title-area" id="courseTitle">Course title</div>
                <div class="ondate-area">
                    <input type="date" id="attendanceDate">
                </div>
                <div class="status-select-area">
                    <select id="bulkStatus">
                        <option value="">Select Status</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                    </select>
                </div>
                <button id="submitAttendance">Submit Attendance</button>
            </div>
        </div>

        <!-- Student List -->
        <div class="studentlist-area" id="studentlistarea">
            <label>STUDENT DETAILS</label>
            <table class="student-table" id="studentDetails">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Session ID</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr class="student-entry"
                            data-course-id="<?php echo htmlspecialchars($session['course_id']); ?>"
                            data-session-id="<?php echo htmlspecialchars($session['session_id']); ?>"
                            data-student-id="<?php echo htmlspecialchars($session['student_id']); ?>"
                            data-on-date="<?php echo htmlspecialchars($session['on_date']); ?>">

                            <td><?php echo htmlspecialchars($session['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($session['session_id']); ?></td>
                            <td class="status-cell"><?php echo htmlspecialchars($session['status']); ?></td>
                            <td>
                                <select class="status-select">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                </select>
                                <button class="btnUpdateStatus">Save</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden Inputs -->
    <input type="hidden" id="hiddenFacId" value="<?php echo htmlspecialchars($student_id); ?>">
    <input type="hidden" id="hiddenSelectedCourseID" value="-1">

    <!-- JavaScript -->
    <script src="js/jquery.js"></script>
    <script src="js/attenda_update.js"></script>
    <script src="js/logout.js"></script>
    <script src="js/navb.js"></script>
</body>
</html>
