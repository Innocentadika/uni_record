// script.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', (e) => {
        const studentName = document.getElementById('student_name').value;
        const rollNo = document.getElementById('roll_no').value;
        const courseCode = document.getElementById('course_code').value;
        const sessionId = document.getElementById('session_id').value;
        
        // Client-side validation (if needed)
        if (!studentName || !rollNo || !courseCode || !sessionId) {
            e.preventDefault();
            alert('Please fill all fields!');
        }
    });
});
