<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();
$files = [];

// Start session to fetch student data
session_start();
$student_id = $_SESSION['student_id'] ?? ''; // Assuming student's ID is stored in session

// Ensure the student is logged in
if (!$student_id) {
    die("You must be logged in to view this page.");
}

// Fetch the course_id from the course_registration table
try {
    $stmt = $dbo->conn->prepare("SELECT course_id FROM course_registration WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $student_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        die("No course found for this student.");
    }

    $student_course_id = $course['course_id']; // Get the student's registered course_id
} catch (PDOException $e) {
    die("Error fetching course registration: " . $e->getMessage());
}

// Fetch assignments for the student's course_id only
try {
    $stmt = $dbo->conn->prepare("
        SELECT id, course_id, assignment1, assignment2, assignment3, cat 
        FROM course_details 
        WHERE course_id = :course_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute([':course_id' => $student_course_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching files: " . $e->getMessage());
}

function downloadFile($content, $filename, $type) {
    header("Content-Description: File Transfer");
    header("Content-Type: $type");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Length: " . strlen($content));
    echo $content;
    exit;
}

if (isset($_GET['download']) && isset($_GET['type'])) {
    $id = $_GET['download'];
    $type = $_GET['type'];

    $allowedFields = ['assignment1', 'assignment2', 'cat'];
    if (in_array($type, $allowedFields)) {
        // Query to ensure the student can only download files for their registered course_id
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
</head>
<body>
    <div class="container">
        <h1>Welcome to Student Dashboard</h1>
        <p>Click to download the uploaded files for <strong><?php echo htmlspecialchars($student_course_id); ?></strong>:</p>

        <table class="file-table">
            <thead>
                <tr>
                    <th>Unit code</th>
                    <th>Assignment 1</th>
                    <th>Assignment 2</th>
                    <th>CAT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($files) > 0): ?>
                    <?php foreach ($files as $index => $row): ?>
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
                    <tr><td colspan="5">No files available for your course.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="js/student.js"></script>
</body>
</html>
