$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        let username = $('#username').val();
        let password = $('#password').val();

        $.post('php/login.php', { username: username, password: password }, function(data) {
            if (data.status === 'success') {
                console.log(data.role);
                if (data.role === 'admin'){
                    window.location.href = 'services.php';
                }else{
                    window.location.href = 'clinics.html';
                }
            } else {
                alert('خطأ في اسم المستخدم أو كلمة المرور');
            }
        }, 'json');
    });
});
