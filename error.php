<?php
session_start();

$page_title = "سجل الأخطاء";
$page_subtitle = "عرض وإدارة أخطاء النظام";
$breadcrumb = [
    ['title' => 'سجل الأخطاء', 'url' => 'error.php', 'active' => true]
];

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $page_title = "خطأ في النظام";
    $page_subtitle = "حدث خطأ في النظام";
}

include 'includes/admin_header.php';
?>
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            color: #dc3545;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-message {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-details {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: right;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #495057;
        }
        .btn-home {
            background: #007bff;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .error-code {
            background: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">⚠️</div>
            <h1 class="error-title">حدث خطأ في النظام</h1>
            
            <?php
            $error_type = isset($_GET['error']) ? $_GET['error'] : 'unknown';
            $error_message = isset($_GET['message']) ? $_GET['message'] : 'حدث خطأ غير معروف';
            
            // ترجمة أنواع الأخطاء
            $error_types = [
                'db_connection' => 'خطأ في الاتصال بقاعدة البيانات',
                'db_query' => 'خطأ في استعلام قاعدة البيانات',
                'auth' => 'خطأ في المصادقة',
                'permission' => 'خطأ في الصلاحيات',
                'file_not_found' => 'الملف غير موجود',
                'session' => 'خطأ في الجلسة',
                'unknown' => 'خطأ غير معروف'
            ];
            
            $error_title = isset($error_types[$error_type]) ? $error_types[$error_type] : $error_types['unknown'];
            ?>
            
            <p class="error-message">
                <?php echo htmlspecialchars($error_title); ?>
            </p>
            
            <div class="error-details">
                <strong>تفاصيل الخطأ:</strong><br>
                <?php echo htmlspecialchars($error_message); ?>
                <br><br>
                <strong>نوع الخطأ:</strong> <span class="error-code"><?php echo htmlspecialchars($error_type); ?></span><br>
                <strong>الوقت:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                <strong>المعرف:</strong> <?php echo uniqid(); ?>
            </div>
            
            <a href="index.php" class="btn-home">العودة للصفحة الرئيسية</a>
        </div>
    </div>

    <script>
        // إرسال تقرير الخطأ تلقائياً (اختياري)
        function reportError() {
            const errorData = {
                type: '<?php echo $error_type; ?>',
                message: '<?php echo addslashes($error_message); ?>',
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href
            };
            
            // يمكن إرسال البيانات إلى سيرفر للتسجيل
            fetch('php/log_error.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(errorData)
            }).catch(err => console.log('Error reporting failed:', err));
        }
        
        // تشغيل تقرير الخطأ عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', reportError);
    </script>
<?php include 'includes/admin_footer.php'; ?>