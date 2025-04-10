$(document).ready(function () {
    // Filter student list by course
    $('#ddlclass').on('change', function () {
        const selectedCourse = $(this).val();
        $('.student-entry').each(function () {
            const courseId = $(this).data('course-id');
            $(this).toggle(courseId === selectedCourse || selectedCourse === "");
        });
    });

    // Update individual student status
    $('.btnUpdateStatus').on('click', function () {
        const $row = $(this).closest('tr');
        const student_id = $row.data('student-id');
        const course_id = $row.data('course-id');
        const session_id = $row.data('session-id');
        const on_date = $row.data('on-date');
        const status = $row.find('.status-select').val();

        $.ajax({
            url: 'attendreg/update_status.php',
            type: 'POST',
            data: {
                student_id: student_id,
                course_id: course_id,
                session_id: session_id,
                on_date: on_date,
                status: status
            },
            success: function (response) {
                let res = JSON.parse(response);
                if (res.success) {
                    alert("Status updated successfully.");
                    $row.find('.status-cell').text(status);
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function () {
                alert("Request failed.");
            }
        });
    });

    // Bulk Submit Attendance
    $('#submitAttendance').on('click', function () {
        const date = $('#attendanceDate').val();
        const status = $('#bulkStatus').val();

        if (!date || !status) {
            alert("Please select a date and status before submitting.");
            return;
        }

        const studentData = [];
        $('.student-entry:visible').each(function () {
            studentData.push({
                student_id: $(this).data('student-id'),
                course_id: $(this).data('course-id'),
                session_id: $(this).data('session-id'),
                on_date: date,
                status: status
            });
        });

        if (studentData.length === 0) {
            alert("No students to update.");
            return;
        }

        $.ajax({
            url: 'attendreg/bulk_update.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ students: studentData }),
            success: function (response) {
                let res = JSON.parse(response);
                if (res.success) {
                    alert("Bulk attendance submitted successfully.");
                    $('.student-entry:visible .status-cell').text(status);
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function () {
                alert("Failed to submit attendance.");
            }
        });
    });
});
