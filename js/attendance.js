$(document).ready(function () {
    // Handle course selection
    $(document).on("click", ".classcard", function () {
        var courseId = $(this).data("course-id");
        var courseText = $(this).text();

        // Update course details UI
        $("#courseCode").text(courseText.split(" - ")[0]);
        $("#courseTitle").text(courseText.split(" - ")[1]);
        $("#hiddenSelectedCourseID").val(courseId);

        fetchStudents(courseId);
    });

    function fetchStudents(courseId) {
        $.ajax({
            url: "ajaxhandler/getStudents.php",
            type: "POST",
            dataType: "json",
            data: { course_id: courseId },
            success: function (response) {
                if (response.status === "OK") {
                    let studentListHtml = '';
                    response.data.forEach((student, index) => {
                        studentListHtml += `
                            <div class="studentdetails">
                                <div class="slno-area">${index + 1}</div>
                                <div class="rollno-area">${student.roll_no}</div>
                                <div class="name-area">${student.name}</div>
                                <div class="checkbox-area">
                                    <input type="checkbox" class="attendanceCheckbox" data-student-id="${student.student_id}">
                                </div>
                            </div>
                        `;
                    });
                    $("#studentDetails").html(studentListHtml);
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Failed to fetch student data.");
            }
        });
    }

    // Submit attendance
    $("#submitAttendance").on("click", function () {
        let courseId = $("#hiddenSelectedCourseID").val();
        let attendanceDate = $("#attendanceDate").val();
        let attendanceData = [];

        $(".attendanceCheckbox:checked").each(function () {
            attendanceData.push({
                student_id: $(this).data("student-id"),
                status: "PRESENT"
            });
        });

        if (!courseId || !attendanceDate) {
            alert("Please select course and date.");
            return;
        }

        $.ajax({
            url: "ajaxhandler/saveAttendance.php",
            type: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify({
                course_id: courseId,
                date: attendanceDate,
                attendance: attendanceData
            }),
            success: function (response) {
                if (response.status === "OK") {
                    alert("Attendance recorded successfully!");
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function () {
                alert("An error occurred while saving attendance.");
            }
        });
    });

    // Logout
    $("#btnLogout").on("click", function () {
        window.location.href = "logout.php";
    });
});
