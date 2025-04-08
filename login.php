<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php";

$dbo = new Database();

// Test database connection
try {
    $dbo->conn->query("SELECT 1");
} catch (PDOException $e) {
    echo json_encode(['status' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "verifyUser") {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    if (empty($user_name) || empty($password)) {
        echo json_encode(['status' => 'Username and password are required!']);
        exit;
    } else {
        $query = "SELECT * FROM faculty_details WHERE user_name = :user_name LIMIT 1";
        $stmt = $dbo->conn->prepare($query);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];

            echo json_encode(['status' => 'ALL OK']);
            exit;
        } else {
            echo json_encode(['status' => 'Invalid username or password!']);
            exit;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/loader.css">
    <title>LoginPage</title>
</head>
<body>
    <div class="loginform">
        <div class="inputgroup topmarginlarge">
            <input type="text" id="txtUsername" name="user_name" required>
            <label for="txtUsername" id="lblUsername">USER NAME</label>
        </div>

        <div class="inputgroup topmarginlarge">
            <input type="password" id="txtPassword" name="password" required>
            <label for="txtPassword" id="lblPassword">PASSWORD</label>
        </div>

        <div class="divcallforaction topmarginlarge">
            <button type="button" class="btnlogin inactivecolor" id="btnLogin">LOGIN</button>
        </div>  

        <div class="diverror topmarginlarge" id="diverror">
            <label class="errormessage" id="errormessage"><?php echo $error_message; ?></label>
        </div>
    </div>

    <div class="lockscreen" id="lockscreen">
        <div class="spinner" id="spinner"></div>
        <label class="lblwait topmargin" id="lblwait">PLEASE WAIT</label>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
