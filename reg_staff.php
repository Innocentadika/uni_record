<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/uni_record/database/database.php";

$dbo = new Database();
$staff_id = $password = $confirm_password = $name = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if (empty($staff_id) || empty($password) || empty($confirm_password) || empty($name)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if staff_id already exists in the faculty_details table
        $checkQuery = "SELECT staff_id FROM staff_details WHERE staff_id = :staff_id";
        $stmt = $dbo->conn->prepare($checkQuery);
        $stmt->execute([':staff_id' => $staff_id]);

        if ($stmt->fetch()) {
            $error_message = "This faculty member is already registered!";
        } else {
            // Hash the password before storing it in the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $insertQuery = "INSERT INTO staff_details (staff_id, password, name) VALUES (:staff_id, :password, :name)";
                $insertStmt = $dbo->conn->prepare($insertQuery);
                $insertStmt->execute([
                    ":staff_id" => $staff_id,
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9f9fb;
      color: #333;
      line-height: 1.6;
    }

    .hed {
      background: #fff;
      padding: 20px 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      flex-wrap: wrap;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    nav a {
      text-decoration: none;
      color: #555;
      font-weight: 500;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #1abc9c;
    }

    .dropdown {
      position: relative;
      cursor: pointer;
    }

    .dropdown-toggle {
      color: #555;
      font-weight: 500;
      text-decoration: none;
      padding: 5px;
    }

    .dropdown-menu {
      position: absolute;
      top: 30px;
      right: 0;
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 5px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      width: 150px;
      display: none;
      z-index: 1000;
    }

    .dropdown-menu a {
      display: block;
      padding: 10px 15px;
      text-decoration: none;
      color: #333;
      transition: background 0.2s;
    }

    .dropdown-menu a:hover {
      background-color: #f0f0f0;
    }

    .dropdown:hover .dropdown-menu {
      display: block;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
      .menu-toggle {
        display: block;
      }

      nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        width: 100%;
        display: none;
        margin-top: 15px;
      }

      nav.active {
        display: flex;
      }

      .dropdown-menu {
        left: 0;
        right: auto;
      }
    }
  </style>
</head>
<body>

  <header class="hed">
    <div class="logo">SmartLearn</div>
    <div class="menu-toggle" id="menu-toggle">☰</div>
    <nav id="navbar">
      <a href="./index.html">Home</a>
      <a href="#">Courses</a>
      <a href="#">About</a>
      <a href="#">Contact</a>
      <div class="dropdown">
        <span class="dropdown-toggle">Login ▾</span>
        <div class="dropdown-menu">
          <a href="./login.php">Student</a>
          <a href="./lecture_log.php">Lecturer</a>
          <a href="./staff_log.php">Admin</a>
        </div>
      </div>
    </nav>
  </header>

  <script>
    // Toggle navigation on mobile
    const menuToggle = document.getElementById('menu-toggle');
    const navbar = document.getElementById('navbar');

    menuToggle.addEventListener('click', () => {
      navbar.classList.toggle('active');
    });
  </script>
  
    <div class="form-container">
        <h1>Faculty Registration</h1>

        <form id="facultyForm" action="" method="POST">
            <div class="input-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="your official names" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="input-group">
                <label for="staff_id">Registration ID:</label>
                <input type="text" id="staff_id" name="staff_id" placeholder="set registration ID" value="<?php echo htmlspecialchars($staff_id); ?>" required>
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
</body>
</html>
