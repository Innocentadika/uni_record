// JavaScript for client-side validation
function validateForm() {
    let password = document.getElementById("password").value;
    let confirm_password = document.getElementById("confirm_password").value;
    let errorMessage = document.getElementById("error-message");

    // Clear any previous error messages
    errorMessage.innerHTML = '';

    // Check if the passwords match
    if (password !== confirm_password) {
        errorMessage.innerHTML = "Passwords do not match.";
        return false;
    }

    return true; // Form is valid
}

// Attach the validation function to the form's submit event
document.getElementById("facultyForm").addEventListener("submit", function(event) {
    if (!validateForm()) {
        event.preventDefault(); // Prevent form submission if validation fails
    }
});
