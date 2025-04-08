<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/schoolpro/database/database.php"; // Include your database connection file

// Fetch sessions
$query_sessions = "SELECT * FROM session_details";
$stmt_sessions = $dbo->conn->prepare($query_sessions);
$stmt_sessions->execute();
$sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses assigned to the faculty
$query_courses = "SELECT c.id, c.code, c.title FROM course_details c 
                  JOIN course_allotment ca ON c.id = ca.course_id 
                  WHERE ca.faculty_id = :facid";
$stmt_courses = $dbo->conn->prepare($query_courses);
$stmt_courses->bindParam(':facid', $facid);
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/attendance.css">
    <title>Attendance Page</title>
</head>
<body>
    <h1>Hello Faculty</h1>
    <button id="btnLogout">LOGOUT</button>
     <div class="page">
        <div class="header-area">
            <div class="logo-area"> <h2 class="logo">ATTENDANCE APP</h2></div>
            <div class="logout-area"><button class="btnlogout" id="btnLogout">LOGOUT</button></div>
        </div>
        <div class="session-area">
              <div class="label-area"><label>SESSION</label></div>
              <div class="dropdown-area">
                <select class="ddlclass" id="ddlclass">
                   <option>SELECT ONE</option>
                   <?php foreach ($sessions as $session): ?>
                       <option value="<?php echo $session['id']; ?>"><?php echo $session['year'] . " " . $session['term']; ?></option>
                   <?php endforeach; ?>
                </select>
              </div>
        </div>

        <div class="classlist-area" id="classlistarea">
          <?php foreach ($courses as $course): ?>
              <div class="classcard" data-course-id="<?php echo $course['id']; ?>"><?php echo $course['code'] . " - " . $course['title']; ?></div>
          <?php endforeach; ?>
        </div>

        <div class="classdetails-area" id="classdetailsarea">
            <div class="classdetails">
                <div class="code-area" id="courseCode">Select a course</div>
                <div class="title-area" id="courseTitle">Course title</div>
                <div class="ondate-area">
                    <input type="date" id="attendanceDate">
                </div>
            </div>
        </div>
        
        <div class="studentlist-area" id="studentlistarea">
            <div class="studentlist"><label>STUDENT LIST</label></div>
            <div class="studentdetails" id="studentDetails"></div>
        </div>
     </div>
     
     <input type="hidden" id="hiddenFacId" value=<?php echo($facid) ?>>
     <input type="hidden" id="hiddenSelectedCourseID" value="-1">
     
    <script src="js/jquery.js"></script>
    <script src="js/attendance.js"></script>
</body>
</html>
