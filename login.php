<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/schoolpro/database/database.php";

$dbo = new Database();
$student_id = $password = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($student_id) || empty($password)) {
        $error_message = "Student ID and password are required!";
    } else {

        $checkQuery = "SELECT student_id, password FROM faculty_details WHERE student_id = :student_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // password_verify() works with hashed passwords
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['student_id'] = $student_id;
                header("Location: stu_dashboard.php");
                exit;
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "Student ID not found.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/registration.css">
</head>
<body>
    <div class="form-container">
        <h1>Login</h1>

        <form id="loginForm" action="" method="POST">
            <div class="input-group">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" placeholder="Enter your student ID" required>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="form-actions">
                <button type="submit">Login</button>
            </div>
            <div class="" float="right">
                <a href="./reset_pass.php">Forgot password</a>
            </div>

            <?php if ($error_message != ""): ?>
                <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="js/login.js"></script>
</body>
</html>
