<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php"; // Adjust the path if needed

// Create an instance of the Database class
$database = new Database();


$dbo = $database->conn;

$student_name = $roll_no = $course_id = $session_id = "";
$error_message = $success_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize form inputs
    $student_name = trim($_POST['student_name']);
    $roll_no = trim($_POST['roll_no']);
    $course_id = trim($_POST['course_id']);
    $session_id = trim($_POST['session_id']);

    if (empty($student_name) || empty($roll_no) || empty($course_id) || empty($session_id)) {
        $error_message = "All fields are required!";
    } else {
        try {
            $c = "SELECT id FROM course_details WHERE code = :course_id";
            $s = $dbo->prepare($c);
            $s->execute([":course_id" => $course_id]);
            
            // If no course with the provided code exists, display an error
            if ($s->rowCount() == 0) {
                $error_message = "Course code does not exist!";
            } else {
                $course_id = $s->fetch(PDO::FETCH_ASSOC)['id']; // Get course_id

                // Insert data into student_details table
                $c = "INSERT INTO student_details (roll_no, name) VALUES (:roll_no, :name)";
                $s = $dbo->prepare($c);
                $s->execute([
                    ":roll_no" => $roll_no,
                    ":name" => $student_name
                ]);

                // Insert data into course_registration table
                $c = "INSERT INTO course_registration (student_id, course_id, session_id) VALUES 
                    ((SELECT id FROM student_details WHERE roll_no = :roll_no), 
                    :course_id, 
                    :session_id)";
                $s = $dbo->prepare($c);
                $s->execute([
                    ":roll_no" => $roll_no,
                    ":course_id" => $course_id,
                    ":session_id" => $session_id
                ]);

                $success_message = "Student and course registration added successfully!";
            }
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
    <title>Admin Add Data</title>
    <link rel="stylesheet" href="css/subform.css"> <!-- External CSS file -->
</head>
<body>
    <div class="form-container">
        <h1>Admin Add Data</h1>

        <!-- Form submission -->
        <form action="" method="POST">
            <div class="input-group">
                <label for="student_name">Student Name:</label>
                <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" required>
            </div>

            <div class="input-group">
                <label for="roll_no">Roll Number:</label>
                <input type="text" id="roll_no" name="roll_no" value="<?php echo htmlspecialchars($roll_no); ?>" required>
            </div>

            <div class="input-group">
                <label for="course_id">Course Code:</label>
                <input type="text" id="course_id" name="course_id" value="<?php echo htmlspecialchars($course_code); ?>" required>
            </div>

            <div class="input-group">
                <label for="session_id">Session ID:</label>
                <input type="text" id="session_id" name="session_id" value="<?php echo htmlspecialchars($session_id); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit">Add Data</button>
            </div>

            <!-- Display error or success messages -->
            <?php if ($error_message != ""): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if ($success_message != ""): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="js/subform.js"></script>
</body>
</html>
