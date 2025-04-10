document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', () => {
        const fileName = input.files[0] ? input.files[0].name : "No file selected";
        input.previousElementSibling.textContent += ` (${fileName})`;
    });
});
