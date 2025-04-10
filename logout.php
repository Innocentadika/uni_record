<?php
require_once "database/database.php";
$dbo = new Database();
$message = "";

// Fetch all unique course_ids
$courses = $dbo->conn->query("SELECT DISTINCT course_id FROM course_registration")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST["course_id"];

    // Handle all 3 fields
    $fields = ["assignment1", "assignment2", "cat"];
    $data = [];

    foreach ($fields as $field) {
        if (!empty($_FILES[$field]["tmp_name"])) {
            $fileData = file_get_contents($_FILES[$field]["tmp_name"]);
            $data[$field] = $fileData;
        } else {
            $data[$field] = null;
        }
    }

    $stmt = $dbo->conn->prepare("
        INSERT INTO course_details (course_id, assignment1, assignment2, cat)
        VALUES (:course_id, :assignment1, :assignment2, :cat)
    ");

    $stmt->bindParam(":course_id", $course_id);
    $stmt->bindParam(":assignment1", $data["assignment1"], PDO::PARAM_LOB);
    $stmt->bindParam(":assignment2", $data["assignment2"], PDO::PARAM_LOB);
    $stmt->bindParam(":cat", $data["cat"], PDO::PARAM_LOB);

    if ($stmt->execute()) {
        $message = "Files uploaded successfully!";
    } else {
        $message = "Error uploading files.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="upload-container">
        <h2>Upload Course Files</h2>

        <?php if ($message): ?>
            <p class="status-msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="course_id">Select Course:</label>
            <select name="course_id" id="course_id" required>
                <option value="">-- Choose Course ID --</option>
                <?php foreach ($courses as $cid): ?>
                    <option value="<?php echo htmlspecialchars($cid); ?>"><?php echo htmlspecialchars($cid); ?></option>
                <?php endforeach; ?>
            </select>

            <div class="upload-section">
                <label>Assignment 1 (PDF or DOCX):</label>
                <input type="file" name="assignment1" accept=".pdf,.doc,.docx">
            </div>

            <div class="upload-section">
                <label>Assignment 2 (PDF or DOCX):</label>
                <input type="file" name="assignment2" accept=".pdf,.doc,.docx">
            </div>

            <div class="upload-section">
                <label>CAT (PDF or DOCX):</label>
                <input type="file" name="cat" accept=".pdf,.doc,.docx">
            </div>

            <button type="submit">Upload Files</button>
        </form>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
