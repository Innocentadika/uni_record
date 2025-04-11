<?php
session_start(); 
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

// Ensure the user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_log.php");
    exit;
}

$dbo = new Database();

$student_id = $password = $confirm_password = $name = $course_id = $session_id = $current_course = $department = $semester = $year = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id']; // Get student_id from the form submission

    try {
        // Step 1: Fetch student details based on the student_id
        $studentQuery = "SELECT * FROM student_details WHERE student_id = :student_id";
        $studentStmt = $dbo->conn->prepare($studentQuery);
        $studentStmt->execute([":student_id" => $student_id]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            // Pre-fill the form values with current data
            $name = $student['name'];
        } else {
            $error_message = "No student found with that ID.";
        }

        // Step 2: Fetch course registration details (assuming they are related by student_id)
        $courseQuery = "SELECT * FROM course_registration WHERE student_id = :student_id";
        $courseStmt = $dbo->conn->prepare($courseQuery);
        $courseStmt->execute([":student_id" => $student_id]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            // Pre-fill course-related fields
            $course_id = $course['course_id'];
            $session_id = $course['session_id'];
            $current_course = $course['current_course'];
            $department = $course['department'];
        }

        // Step 3: Fetch session details (optional based on student_id)
        $sessionQuery = "SELECT * FROM session_details WHERE student_id = :student_id";
        $sessionStmt = $dbo->conn->prepare($sessionQuery);
        $sessionStmt->execute([":student_id" => $student_id]);
        $session = $sessionStmt->fetch(PDO::FETCH_ASSOC);

        if ($session) {
            // Pre-fill session-related fields
            $semester = $session['semester'];
            $year = $session['year'];
        }

    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle form submission (update data)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Capture the form data
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $name = trim($_POST['name']);
    $course_id = trim($_POST['course_id']);
    $session_id = trim($_POST['session_id']);
    $current_course = trim($_POST['current_course']);
    $department = trim($_POST['department']);
    $semester = trim($_POST['semester']);
    $year = trim($_POST['year']);

    // Validate required fields
    if (empty($student_id) || empty($name) || empty($course_id) || empty($session_id) || empty($department) || empty($semester) || empty($year)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        try {
            // Update password if provided
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $passwordUpdate = "UPDATE faculty_details SET password = :password WHERE student_id = :student_id";
                $passwordStmt = $dbo->conn->prepare($passwordUpdate);
                $passwordStmt->execute([":student_id" => $student_id, ":password" => $hashedPassword]);
            }

            // Update student details
            $studentUpdate = "UPDATE student_details SET name = :name WHERE student_id = :student_id";
            $studentStmt = $dbo->conn->prepare($studentUpdate);
            $studentStmt->execute([":student_id" => $student_id, ":name" => $name]);

            // Update course registration details
            $courseUpdate = "UPDATE course_registration SET course_id = :course_id, session_id = :session_id, current_course = :current_course, department = :department WHERE student_id = :student_id";
            $courseStmt = $dbo->conn->prepare($courseUpdate);
            $courseStmt->execute([
                ":student_id" => $student_id,
                ":course_id" => $course_id,
                ":session_id" => $session_id,
                ":current_course" => $current_course,
                ":department" => $department
            ]);

            // Update course details
            $courseUpdate = "UPDATE course_details SET course_id = :course_id, session_id = :session_id, semester = :semester WHERE student_id = :student_id";
            $courseStmt = $dbo->conn->prepare($courseUpdate);
            $courseStmt->execute([
                ":student_id" => $student_id,
                ":course_id" => $course_id,
                ":session_id" => $session_id,
                ":semester" => $semester
            ]);

            // Update attendance details
            $courseUpdate = "UPDATE attendance_details SET course_id = :course_id, session_id = :session_id WHERE student_id = :student_id";
            $courseStmt = $dbo->conn->prepare($courseUpdate);
            $courseStmt->execute([
                ":student_id" => $student_id,
                ":course_id" => $course_id,
                ":session_id" => $session_id
            ]);

            // Update session details
            $sessionUpdate = "UPDATE session_details SET semester = :semester, year = :year WHERE student_id = :student_id";
            $sessionStmt = $dbo->conn->prepare($sessionUpdate);
            $sessionStmt->execute([
                ":semester" => $semester,
                ":year" => $year,
                ":student_id" => $student_id
            ]);

            $success_message = "Student data updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
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
    <link rel="stylesheet" href="css/navb.css">
</head>
<body>

<div>
<div class="nvb">
<nav class="navbar">
    <div class="logo">SmartLearn</div>
    <ul class="nav-links">
      <li><a href="./index.html">Home</a></li>
      <li><a href="./registration.php">Dashboard</a></li>
      <li><a href="./reg_update.php">Student update</a></li>
      <li><a href="./reg_lecturer.php">Register lecture</a></li>
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
    <div class="form-container">
        <h1>Update Faculty Registration</h1>

        <!-- Step 2: First input field for student_id -->
        <form id="facultyForm" action="" method="POST">
            <div class="input-group">
                <label for="student_id">Enter Student ID to fetch details:</label>
                <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit">Fetch Student Data</button>
            </div>
        </form>

        <!-- Step 3: Show the editable form after student_id is submitted -->
        <?php if ($student_id && $student): ?>
            <h2>Edit Student Data</h2>
            <form id="facultyForm" action="" method="POST">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">

                <div class="input-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="input-group">
                    <label for="current_course">Course Title:</label>
                    <input type="text" id="current_course" name="current_course" value="<?php echo htmlspecialchars($current_course); ?>" required>
                </div>

                <div class="input-group">
                    <label for="course_id">Unit Title:</label>
                    <input type="text" id="course_id" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>" required>
                </div>

                <div class="input-group">
                    <label for="department">Faculty/Department:</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" required>
                </div>

                <div class="input-group">
                    <label for="session_id">Active Session:</label>
                    <select class="slt" id="session_id" name="session_id" required>
                        <option value="online" <?php echo $session_id == 'online' ? 'selected' : ''; ?>>Online</option>
                        <option value="part-time" <?php echo $session_id == 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                        <option value="full-time" <?php echo $session_id == 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                    </select>
                </div>

                <div class="enrolment">
                    <div class="input-grp">
                        <label for="year">Year:</label>
                        <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" required>
                    </div>

                    <div class="input-grp">
                        <label for="semester">Semester:</label>
                        <input type="number" id="semester" name="semester" min="1" max="3" value="<?php echo htmlspecialchars($semester); ?>" required>
                    </div>
                </div>

                <div class="enrolment">
                    <div class="input-grp">
                        <label for="password">Set password:</label>
                        <input type="password" id="password" name="password" placeholder="password">
                    </div>

                    <div class="input-grp">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="repeat password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update">Update Faculty</button>
                </div>

                <?php if ($error_message != ""): ?>
                    <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if ($success_message != ""): ?>
                    <div id="success-message" class="success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <footer style="position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; background-color: #f1f1f1; padding: 10px;">
  © 2025 SmartLearn. All rights reserved.
</footer>

    <script src="js/register.js"></script>
    <script src="js/navb.js"></script>
    <script src="js/logout.js"></script>
</body>
</html>
