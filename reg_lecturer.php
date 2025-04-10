<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();
$lecturer_id = $password = $confirm_password = $name = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lecturer_id = trim($_POST['lecturer_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if (empty($lecturer_id) || empty($password) || empty($confirm_password) || empty($name)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if lecturer_id already exists in the faculty_details table
        $checkQuery = "SELECT lecturer_id FROM faculty_details WHERE lecturer_id = :lecturer_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->execute([':lecturer_id' => $lecturer_id]);

        if ($stmt->fetch()) {
            $error_message = "This faculty member is already registered!";
        } else {
            // Hash the password before storing it in the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $insertQuery = "INSERT INTO faculty_details (lecturer_id, password, name) 
                                VALUES (:lecturer_id, :password, :name)";
                $insertStmt = $dbo->conn->prepare($insertQuery);
                $insertStmt->execute([
                    ":lecturer_id" => $lecturer_id,
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
</head>
<body>
    <div class="form-container">
        <h1>Faculty Registration</h1>

        <form id="facultyForm" action="" method="POST">
            <div class="input-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="your official names" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="input-group">
                <label for="lecturer_id">Registration ID:</label>
                <input type="text" id="lecturer_id" name="lecturer_id" placeholder="set registration ID" value="<?php echo htmlspecialchars($lecturer_id); ?>" required>
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

    <script src="js/register.js"></script>
</body>
</html>
