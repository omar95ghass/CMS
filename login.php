<?php
include 'config/schema_creation.php';
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة الطوابير</title>
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        .login-title {
            color: #333;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-top: 20px;
        }
        
        .loading {
            display: none;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            min-width: 100%;
        }
        
        .footer-text {
            color: white;
            text-align: center;
            margin-top: 30px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🏥</div>
        <h1 class="login-title">تسجيل الدخول</h1>
        <p class="login-subtitle">نظام إدارة الدور v3.1</p>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" class="form-control" id="username" name="username" required 
                       placeholder="أدخل اسم المستخدم">
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="أدخل كلمة المرور">
            </div>
            <button type="submit" class="btn btn-login">
                <span class="btn-text">تسجيل الدخول</span>
                <span class="loading">
                    <span class="spinner"></span>
                    جاري التحقق...
                </span>
            </button>
        </form>
        
        <div id="alertBox" class="alert alert-dismissible" style="display: none;"></div>
    </div>
    
    <footer>
        <div class="footer-text">
            <p>&copy; <?php echo date('Y'); ?> نظام إدارة الطوابير - جميع الحقوق محفوظة</p>
        </div>
    </footer>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const username = $('#username').val();
                const password = $('#password').val();
                
                if (!username || !password) {
                    showAlert('يرجى ملء جميع الحقول', 'warning');
                    return;
                }
                
                // إظهار حالة التحميل
                $('.btn-text').hide();
                $('.loading').show();
                $('.btn-login').prop('disabled', true);
                
                $.post('php/login.php', { 
                    username: username, 
                    password: password 
                }, function(data) {
                    if (data.status === 'success') {
                        showAlert('تم تسجيل الدخول بنجاح', 'success');
                        setTimeout(() => {
                            if (data.role === 'admin') {
                                window.location.href = 'admin_dashboard.php';
                            } else {
                                window.location.href = 'counter.php';
                            }
                        }, 1000);
                    } else {
                        showAlert(data.message || 'خطأ في اسم المستخدم أو كلمة المرور', 'danger');
                    }
                }, 'json')
                .fail(function() {
                    showAlert('خطأ في الاتصال بالخادم', 'danger');
                })
                .always(function() {
                    // إخفاء حالة التحميل
                    $('.btn-text').show();
                    $('.loading').hide();
                    $('.btn-login').prop('disabled', false);
                });
            });
            
            // تأثيرات تفاعلية
            $('.form-control').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                if (!$(this).val()) {
                    $(this).parent().removeClass('focused');
                }
            });
        });
        
        function showAlert(message, type) {
            const alertBox = $('#alertBox');
            alertBox.removeClass('alert-success alert-warning alert-danger');
            alertBox.addClass('alert-' + type);
            alertBox.html(message + '<button type="button" class="btn-close" onclick="$(this).parent().hide()"></button>');
            alertBox.show();
            
            // إخفاء التنبيه تلقائياً بعد 5 ثوان
            setTimeout(() => {
                alertBox.fadeOut();
            }, 5000);
        }
    </script>
</body>
</html>
