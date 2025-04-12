<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/uni_record/database/database.php";

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$dbo = new Database();
$files = [];
$current_user = null;

$student_id = $_SESSION['student_id'] ?? '';

// Fetch current student details
try {
    $stmt = $dbo->conn->prepare("SELECT name, student_id FROM faculty_details WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $student_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching student details: " . $e->getMessage());
}

// Fetch the course_id from the course_registration table
try {
    $stmt = $dbo->conn->prepare("SELECT course_id FROM course_registration WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $student_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        die("No course found for this student.");
    }

    $student_course_id = $course['course_id'];
} catch (PDOException $e) {
    die("Error fetching course registration: " . $e->getMessage());
}

// Fetch files related to the student's course
try {
    $stmt = $dbo->conn->prepare("
        SELECT id, course_id, assignment1, assignment2, cat
        FROM course_details
        WHERE course_id = :course_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':course_id' => $student_course_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching files: " . $e->getMessage());
}

// Download handler
function downloadFile($content, $filename, $type) {
    header("Content-Description: File Transfer");
    header("Content-Type: $type");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Length: " . strlen($content));
    echo $content;
    exit;
}

// If download requested
if (isset($_GET['download']) && isset($_GET['type'])) {
    $id = $_GET['download'];
    $type = $_GET['type'];

    $allowedFields = ['assignment1', 'assignment2', 'cat'];
    if (in_array($type, $allowedFields)) {
        $stmt = $dbo->conn->prepare("SELECT $type FROM course_details WHERE id = :id AND course_id = :course_id");
        $stmt->execute([':id' => $id, ':course_id' => $student_course_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row[$type])) {
            $fileContent = $row[$type];
            $filename = ucfirst($type) . "_file";
            $finfo = finfo_open();
            $mime = finfo_buffer($finfo, $fileContent, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
            downloadFile($fileContent, $filename, $mime);
        } else {
            echo "File not found or not accessible.";
        }
    } else {
        echo "Invalid file type.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/student.css">
    <link rel="stylesheet" href="css/navb.css">
</head>
<body>

<div class="nvb">
<nav class="navbar">
    <div class="logo">SmartLearn</div>
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

<!-- Display logged-in user -->
<div class="user-area">
    <?php if ($current_user): ?>
        <p class="user-welcome">Welcome, <strong><?php echo htmlspecialchars($current_user['name']); ?></strong> (<?php echo htmlspecialchars($current_user['student_id']); ?>)</p>
    <?php endif; ?>
</div>

<div class="container">
    <h1>Student Dashboard</h1>
    <p>Click to download the uploaded files for <strong><?php echo htmlspecialchars($student_course_id); ?></strong>:</p>

    <table class="file-table">
        <thead>
            <tr>
                <th>Unit Code</th>
                <th>Assignment 1</th>
                <th>Assignment 2</th>
                <th>CAT</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($files) > 0): ?>
                <?php foreach ($files as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['course_id']); ?></td>
                        <?php foreach (['assignment1', 'assignment2', 'cat'] as $field): ?>
                            <td>
                                <?php if (!empty($row[$field])): ?>
                                    <a href="?download=<?php echo urlencode($row['id']); ?>&type=<?php echo urlencode($field); ?>" class="download-btn">Download</a>
                                <?php else: ?>
                                    <span class="not-available">N/A</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No files available for your course.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<footer style="position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; background-color: #f1f1f1; padding: 10px;">
  © 2025 SmartLearn. All rights reserved.
</footer>

<script src="js/student.js"></script>
<script src="js/navb.js"></script>
<script src="js/logout.js"></script>

</body>
</html>
