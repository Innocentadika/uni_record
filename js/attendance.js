$(document).ready(function() {
    // Handle course selection
    $(document).on("click", ".classcard", function() {
        var courseId = $(this).data("course-id");
        var courseText = $(this).text();

        // Update course details
        $("#courseCode").text(courseText.split(" - ")[0]);
        $("#courseTitle").text(courseText.split(" - ")[1]);
        $("#hiddenSelectedCourseID").val(courseId);

        // Fetch students for the selected course
        fetchStudents(courseId);
    });

    // Fetch students for a selected course
    function fetchStudents(courseId) {
        $.ajax({
            url: "ajaxhandler/getStudents.php", // This should return students for a specific course
            type: "POST",
            dataType: "json",
            data: { course_id: courseId },
            success: function(response) {
                if (response.status == "OK") {
                    var studentListHtml = '';
                    $.each(response.data, function(index, student) {
                        studentListHtml += `
                            <div class="studentdetails">
                                <div class="slno-area">${index + 1}</div>
                                <div class="rollno-area">${student.roll_no}</div>
                                <div class="name-area">${student.name}</div>
                                <div class="checkbox-area">
                                    <input type="checkbox" class="attendanceCheckbox" data-student-id="${student.id}">
                                </div>
                            </div>
                        `;
                    });
                    $("#studentDetails").html(studentListHtml);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Error fetching student data.");
            }
        });
    }

    // Submit attendance
    $(document).on("click", "#submitAttendance", function() {
        var selectedCourseId = $("#hiddenSelectedCourseID").val();
        var attendanceDate = $("#attendanceDate").val();
        var studentsAttendance = [];

        $(".attendanceCheckbox:checked").each(function() {
            var studentId = $(this).data("student-id");
            studentsAttendance.push({ student_id: studentId, status: 'Present' });
        });

        $.ajax({
            url: "ajaxhandler/saveAttendance.php", // This should save the attendance data
            type: "POST",
            data: {
                course_id: selectedCourseId,
                date: attendanceDate,
                attendance: studentsAttendance
            },
            success: function(response) {
                if (response.status == "OK") {
                    alert("Attendance saved successfully.");
                } else {
                    alert("Error saving attendance.");
                }
            },
            error: function() {
                alert("Error submitting attendance.");
            }
        });
    });
});
