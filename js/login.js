document.getElementById("loginForm").addEventListener("submit", function (e) {
    const studentId = document.getElementById("student_id").value.trim();
    const password = document.getElementById("password").value.trim();

    if (studentId === "" || password === "") {
        e.preventDefault();
        alert("Please fill in both Student ID and Password.");
    }
});
