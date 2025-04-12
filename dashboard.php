<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/uni_record/database/database.php";

// Ensure the user is logged in
if (!isset($_SESSION['lecture_id'])) {
    header("Location: lecture_log.php");
    exit;
}

$dbo = new Database();

// Fetch current staff details
$files = [];
$current_user = null;

$lecture_id = $_SESSION['lecture_id'] ?? '';

try {
    $stmt = $dbo->conn->prepare("SELECT name, lecture_id FROM lecture_details WHERE lecture_id = :lecture_id");
    $stmt->execute([':lecture_id' => $lecture_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching lecturer details: " . $e->getMessage());
}
// end Fetch current staff details

$message = "";

// Fetch available course_id values
$courseOptions = [];
$courseQuery = "SELECT DISTINCT course_id FROM course_registration ORDER BY course_id";
$stmt = $dbo->conn->query($courseQuery);
$courseOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentType = $_POST['assignment_type'] ?? '';
    $selectedCourse = $_POST['course_id'] ?? '';
    $file = $_FILES['assignment_file'] ?? null;

    if (!$assignmentType || !$file || $file['error'] !== UPLOAD_ERR_OK || !$selectedCourse) {
        $message = "Please fill in all fields and upload a valid file.";
    } else {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!in_array($file['type'], $allowedTypes)) {
            $message = "Only PDF or Word files are allowed.";
        } else {
            $fileData = file_get_contents($file['tmp_name']);
            $field = '';

            switch ($assignmentType) {
                case 'assignment1':
                    $field = 'assignment1';
                    break;
                case 'assignment2':
                    $field = 'assignment2';
                    break;
                case 'cat':
                    $field = 'cat';
                    break;
            }

            if ($field) {
                try {
                    $stmt = $dbo->conn->prepare("
                        INSERT INTO course_details (course_id, $field) VALUES (:course_id, :file)
                    ");
                    $stmt->bindParam(':course_id', $selectedCourse);
                    $stmt->bindParam(':file', $fileData, PDO::PARAM_LOB);
                    $stmt->execute();
                    $message = ucfirst($field) . " uploaded successfully for course $selectedCourse!";
                } catch (PDOException $e) {
                    $message = "Upload failed: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Upload Assignments</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/navb.css">
</head>
<body>

<div class="nvb">
<nav class="navbar">
    <div class="logo">SmartLearn</div>
    <ul class="nav-links">
      <li><a href="./index.html">Home</a></li>
      <li><a href="./dashboard.php">Dashboard</a></li>
      <li><a href="./attendance.php">Attendance</a></li>
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

<div class="user-area">
    <?php if ($current_user): ?>
        <p class="user-welcome">Welcome, <strong><?php echo htmlspecialchars($current_user['name']); ?></strong> (<?php echo htmlspecialchars($current_user['lecture_id']); ?>)</p>
    <?php endif; ?>
</div>

    <div class="dashboard-container">
        <h1>Upload Assignment or CAT</h1>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="course_id">Select Course ID:</label>
            <select name="course_id" id="course_id" required>
                <option value="" disabled selected>Select Course</option>
                <?php foreach ($courseOptions as $course): ?>
                    <option value="<?php echo htmlspecialchars($course); ?>">
                        <?php echo htmlspecialchars($course); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="assignment_type">Select Type:</label>
            <select name="assignment_type" id="assignment_type" required>
                <option value="" disabled selected>Select Assignment Type</option>
                <option value="assignment1">Assignment 1</option>
                <option value="assignment2">Assignment 2</option>
                <option value="cat">CAT</option>
            </select>

            <label for="assignment_file">Choose File (PDF or Word):</label>
            <input type="file" name="assignment_file" id="assignment_file" accept=".pdf,.doc,.docx" required>

            <button type="submit">Upload</button>
        </form>
    </div>

    <footer style="position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; background-color: #f1f1f1; padding: 10px;">
  © 2025 SmartLearn. All rights reserved.
</footer>

    <script src="js/dashboard.js"></script>
    <script src="js/navb.js"></script>
<script src="js/Llogout.js"></script>
</body>
</html>
