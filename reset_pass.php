<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/schoolpro/database/database.php";

$dbo = new Database();
$student_id = $new_password = $confirm_password = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($student_id) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if the student_id exists in the database
        $checkQuery = "SELECT student_id FROM faculty_details WHERE student_id = :student_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->execute([':student_id' => $student_id]);

        if (!$stmt->fetch()) {
            $error_message = "Student ID not found!";
        } else {
            // Hash the new password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            try {
                // Update password in the faculty_details table
                $updateQuery = "UPDATE faculty_details SET password = :password WHERE student_id = :student_id";
                $updateStmt = $dbo->conn->prepare($updateQuery);
                $updateStmt->execute([":password" => $hashedPassword, ":student_id" => $student_id]);

                $error_message = "Password successfully updated!";
            } catch (PDOException $e) {
                $error_message = "Error during password reset: " . $e->getMessage();
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
    <title>Password Reset</title>
    <link rel="stylesheet" href="css/registration.css">
</head>
<body>
    <div class="form-container">
        <h1>Reset Password</h1>

        <form id="resetForm" action="" method="POST">
            <div class="input-group">
                <label for="student_id">Registration ID:</label>
                <input type="text" id="student_id" name="student_id" placeholder="Enter your registration ID" required>
            </div>

            <div class="input-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>

            <div class="form-actions">
                <button type="submit">Reset Password</button>
            </div>

            <?php if ($error_message != ""): ?>
                <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="js/register.js"></script>
</body>
</html>
