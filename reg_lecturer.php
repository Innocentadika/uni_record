<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();
$lecture_id = $password = $confirm_password = $name = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lecture_id = trim($_POST['lecture_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if (empty($lecture_id) || empty($password) || empty($confirm_password) || empty($name)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if lecture_id already exists in the faculty_details table
        $checkQuery = "SELECT lecture_id FROM lecture_details WHERE lecture_id = :lecture_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->execute([':lecture_id' => $lecture_id]);

        if ($stmt->fetch()) {
            $error_message = "This faculty member is already registered!";
        } else {
            // Hash the password before storing it in the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $insertQuery = "INSERT INTO lecture_details (lecture_id, password, name) 
                                VALUES (:lecture_id, :password, :name)";
                $insertStmt = $dbo->conn->prepare($insertQuery);
                $insertStmt->execute([
                    ":lecture_id" => $lecture_id,
                    ":password" => $hashedPassword,
                    ":name" => $name
                ]);
                $error_message = "Faculty registered successfully!";
                
            } catch (PDOException $e) {
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
    <title>Lecturer Faculty Registration</title>
    <link rel="stylesheet" href="css/registration.css">
    <link rel="stylesheet" href="css/navb.css">
</head>
<body>

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

    <div class="form-container">
        <h1>Faculty Registration</h1>

        <form id="facultyForm" action="" method="POST">
            <div class="input-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="your official names" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="input-group">
                <label for="lecture_id">Registration ID:</label>
                <input type="text" id="lecture_id" name="lecture_id" placeholder="set registration ID" value="<?php echo htmlspecialchars($lecture_id); ?>" required>
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
    
    <footer style="position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; background-color: #f1f1f1; padding: 10px;">
  © 2025 SmartLearn. All rights reserved.
</footer>

    <script src="js/register.js"></script>
    <script src="js/navb.js"></script>
    <script src="js/Slogout.js"></script>
</body>
</html>
