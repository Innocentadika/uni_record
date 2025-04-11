const btnLogout = document.getElementById('btnLogout');

btnLogout.addEventListener('click', function() {
    
    alert('Logging out...');
    
    window.location.href = '/uni_record/staff_log.php';
});
