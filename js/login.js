function tryLogin() {
    let un = $("#txtUsername").val();
    let pw = $("#txtPassword").val();
    if (un.trim() !== "" && pw.trim() !== "") {
        $.ajax({
            url: "login.php",  // Points to login page
            type: "POST",
            dataType: "json",
            data: { user_name: un, password: pw, action: "verifyUser" }, // Add action to identify the AJAX request
            beforeSend: function() {
                $("#diverror").removeClass("applyerrordiv");
                $("#lockscreen").addClass("applylockscreen");
            },
            success: function(rv) {
                $("#lockscreen").removeClass("applylockscreen");
                if (rv['status'] == "ALL OK") {
                    document.location.replace("attendance.php"); // Redirect to the next page
                } else {
                    $("#diverror").addClass("applyerrordiv");
                    $("#errormessage").text(rv['status']); // Show error message from the server
                }
            },
            error: function() {
                alert("oops something went wrong");
            }
        });
    }
}

$(function() {
    $(document).on("keyup", "input", function() {
        $("#diverror").removeClass("applyerrordiv");
        let un = $("#txtUsername").val();
        let pw = $("#txtPassword").val();
        if (un.trim() !== "" && pw.trim() !== "") {
            $("#btnLogin").removeClass("inactivecolor");
            $("#btnLogin").addClass("activecolor");
        } else {
            $("#btnLogin").removeClass("activecolor");
            $("#btnLogin").addClass("inactivecolor");
        }
    });

    $(document).on("click", "#btnLogin", function() {
        tryLogin(); // Call the login function when the login button is clicked
    });
});
