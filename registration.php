<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();
$student_id = $password = $confirm_password = $name = $course_id = $session_id = $current_course = $department = $semester = $year = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $course_id = trim($_POST['course_id'] ?? '');
    $session_id = trim($_POST['session_id'] ?? '');
    $current_course = trim($_POST['current_course'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $year = trim($_POST['year'] ?? '');

    if (
        empty($student_id) || empty($password) || empty($confirm_password) || empty($name) ||
        empty($course_id) || empty($session_id) || empty($department) || empty($semester) || empty($year)
    ) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if student_id already exists
        $checkQuery = "SELECT student_id FROM student_details WHERE student_id = :student_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->execute([':student_id' => $student_id]);

        if ($stmt->fetch()) {
            $error_message = "This student is already registered!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                // Start transaction
                $dbo->conn->beginTransaction();

                // Insert into faculty_details
                $facultyInsert = "INSERT INTO faculty_details (student_id, password, name) 
                                  VALUES (:student_id, :password, :name)";
                $facultyStmt = $dbo->conn->prepare($facultyInsert);
                $facultyStmt->execute([
                    ":student_id" => $student_id,
                    ":password" => $hashedPassword,
                    ":name" => $name
                ]);

                // Insert into student_details
                $studentInsert = "INSERT INTO student_details (student_id, roll_no, name) 
                                  VALUES (:student_id, :student_id, :name)";
                $studentStmt = $dbo->conn->prepare($studentInsert);
                $studentStmt->execute([
                    ":student_id" => $student_id,
                    ":name" => $name
                ]);

                // Insert into course_registration
                $courseInsert = "INSERT INTO course_registration (student_id, course_id, session_id, current_course, department) 
                                 VALUES (:student_id, :course_id, :session_id, :current_course, :department)";
                $courseStmt = $dbo->conn->prepare($courseInsert);
                $courseStmt->execute([
                    ":student_id" => $student_id,
                    ":course_id" => $course_id,
                    ":session_id" => $session_id,
                    ":current_course" => $current_course,
                    ":department" => $department
                ]);

                // Insert into attendance_details
                $attendanceInsert = "INSERT INTO attendance_details (course_id, session_id, student_id, on_date, status) 
                                     VALUES (:course_id, :session_id, :student_id, CURDATE(), 'PRESENT')";
                $attendanceStmt = $dbo->conn->prepare($attendanceInsert);
                $attendanceStmt->execute([
                    ":student_id" => $student_id,
                    ":course_id" => $course_id,
                    ":session_id" => $session_id
                ]);

                // Check if course_details exists for same course_id and session_id
                $checkCourse = "SELECT 1 FROM course_details WHERE course_id = :course_id AND session_id = :session_id";
                $checkCourseStmt = $dbo->conn->prepare($checkCourse);
                $checkCourseStmt->execute([
                    ":course_id" => $course_id,
                    ":session_id" => $session_id
                ]);

                if (!$checkCourseStmt->fetch()) {
                    $courseDetailsInsert = "INSERT INTO course_details (semester, course_id, session_id, current_course) 
                                            VALUES (:semester, :course_id, :session_id, :current_course)";
                    $courseDetailsStmt = $dbo->conn->prepare($courseDetailsInsert);
                    $courseDetailsStmt->execute([
                        ":semester" => $semester,
                        ":course_id" => $course_id,
                        ":session_id" => $session_id,
                        ":current_course" => $current_course
                    ]);
                }

                // Insert into session_details
                $sessionInsert = "INSERT INTO session_details (semester, year, student_id) 
                                  VALUES (:semester, :year, :student_id)";
                $sessionStmt = $dbo->conn->prepare($sessionInsert);
                $sessionStmt->execute([
                    ":semester" => $semester,
                    ":year" => $year,
                    ":student_id" => $student_id
                ]);

                // Commit transaction
                $dbo->conn->commit();
                $error_message = "Student and course data registered successfully!";
            } catch (PDOException $e) {
                $dbo->conn->rollBack();
                $error_message = "Error during registration: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Registration</title>
    <link rel="stylesheet" href="css/registration.css">
</head>
<body>
    <div class="form-container">
        <h1>Faculty Registration</h1>

        <form id="facultyForm" action="" method="POST">
            <div class="input-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="your official names" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="input-group">
                <label for="current_course">Course Title:</label>
                <input type="text" id="current_course" name="current_course" placeholder="course registering" value="<?php echo htmlspecialchars($current_course); ?>" required>
            </div>

            <div class="input-group">
                <label for="student_id">Registration ID:</label>
                <input type="text" id="student_id" name="student_id" placeholder="set registration ID" value="<?php echo htmlspecialchars($student_id); ?>" required>
            </div>

            <div class="input-group">
                <label for="course_id">Unit Title:</label>
                <input type="text" id="course_id" name="course_id" placeholder="eg DIT 304" value="<?php echo htmlspecialchars($course_id); ?>" required>
            </div>

            <div class="input-group">
                <label for="department">Faculty/Department:</label>
                <input type="text" id="department" name="department" placeholder="your department" value="<?php echo htmlspecialchars($department); ?>" required>
            </div>

            <div class="input-group">
                <label for="session_id">Active Session:</label>
                <select class="slt" id="session_id" name="session_id" required>
                    <option value="" disabled selected>Select session</option>
                    <option value="online" <?php echo $session_id == 'online' ? 'selected' : ''; ?>>Online</option>
                    <option value="part-time" <?php echo $session_id == 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                    <option value="full-time" <?php echo $session_id == 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                </select>
            </div>

            <div class="enrolment">
                <div class="input-grp">
                    <label for="year">Year:</label>
                    <input type="text" id="year" name="year" placeholder="enrolled year" value="<?php echo htmlspecialchars($year); ?>" required>
                </div>

                <div class="input-grp">
                    <label for="semester">Semester:</label>
                    <input type="number" id="semester" name="semester" placeholder="Starting semester" min="1" max="3" value="<?php echo htmlspecialchars($semester); ?>" required>
                </div>
            </div>

            <div class="enrolment">
                <div class="input-grp">
                    <label for="password">Set password:</label>
                    <input type="password" id="password" name="password" placeholder="password" required>
                </div>

                <div class="input-grp">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="repeat password" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Register Faculty</button>
            </div>

            <?php if ($error_message != ""): ?>
                <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="js/register.js"></script>
</body>
</html>
