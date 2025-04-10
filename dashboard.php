<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();
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
</head>
<body>
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

    <script src="js/dashboard.js"></script>
</body>
</html>
