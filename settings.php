<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// التحقق من صلاحيات الإدارة
if ($_SESSION['role'] !== 'admin') {
    header("Location: error.php?error=permission&message=" . urlencode("ليس لديك صلاحية للوصول إلى هذه الصفحة"));
    exit();
}

$page_title = "إعدادات النظام";
$page_subtitle = "إدارة إعدادات النظام والخدمات";
$breadcrumb = [
    ['title' => 'الإعدادات', 'url' => 'settings.php', 'active' => true]
];
include 'includes/admin_header.php';

// معالجة حفظ الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        include 'php/db.php';
        
        $center_name = $_POST['center_name'] ?? '';
        $ticker_messages = $_POST['ticker_messages'] ?? '';
        $printer_name = $_POST['printer_name'] ?? '';
        
        // حفظ الإعدادات في قاعدة البيانات
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        
        $stmt->bind_param("sss", $key, $value, $value);
        
        $key = 'center_name';
        $value = $center_name;
        $stmt->execute();
        
        $key = 'ticker_messages';
        $value = $ticker_messages;
        $stmt->execute();
        
        $key = 'printer_name';
        $value = $printer_name;
        $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        echo '<div class="alert alert-success">تم حفظ الإعدادات بنجاح</div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">خطأ في حفظ الإعدادات: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// جلب الإعدادات الحالية
try {
    include 'php/db.php';
    
    $settings = [];
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $settings = [
        'center_name' => 'مركز خدمة المواطن',
        'ticker_messages' => '✅ مركز خدمة المواطن في دمر\n⚠️ يرجى الاحتفاظ بتذكرة الدور\n📢 لا تخرج من الصالة لتتمكن من سماع النداء\n💡 نتمنى لكم يوماً طيباً',
        'printer_name' => 'EPSON TM-T20'
    ];
}
?>

<style>
    .settings-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        overflow: hidden;
    }
    
    .settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        text-align: center;
    }
    
    .settings-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
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
    
    .textarea-large {
        min-height: 120px;
        resize: vertical;
    }
    
    .btn-save {
        background: linear-gradient(135deg, #28a745 0%, #20bf6b 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        color: white;
    }
    
    .preview-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .preview-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
    }
    
    .ticker-preview {
        background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
        color: white;
        padding: 10px;
        border-radius: 5px;
        overflow: hidden;
        white-space: nowrap;
        animation: ticker 20s linear infinite;
    }
    
    @keyframes ticker {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
</style>

<div class="container">
    <div class="settings-card">
        <div class="settings-header">
            <h2><i class="fas fa-cog"></i> إعدادات النظام</h2>
            <p>إدارة إعدادات المركز والرسائل والطابعة</p>
        </div>
        
        <div class="settings-body">
            <form method="POST" id="settingsForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="center_name">اسم المركز</label>
                            <input type="text" class="form-control" id="center_name" name="center_name" 
                                   value="<?php echo htmlspecialchars($settings['center_name'] ?? 'مركز خدمة المواطن'); ?>" 
                                   placeholder="أدخل اسم المركز">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="printer_name">اسم الطابعة</label>
                            <input type="text" class="form-control" id="printer_name" name="printer_name" 
                                   value="<?php echo htmlspecialchars($settings['printer_name'] ?? 'EPSON TM-T20'); ?>" 
                                   placeholder="أدخل اسم الطابعة">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="ticker_messages">رسائل الشريط المتحرك</label>
                    <textarea class="form-control textarea-large" id="ticker_messages" name="ticker_messages" 
                              placeholder="أدخل الرسائل مفصولة بأسطر جديدة"><?php echo htmlspecialchars($settings['ticker_messages'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">اكتب كل رسالة في سطر منفصل</small>
                </div>
                
                <div class="preview-section">
                    <div class="preview-title">معاينة الشريط المتحرك:</div>
                    <div class="ticker-preview" id="tickerPreview">
                        <span id="previewContent">جاري التحميل...</span>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // تحديث معاينة الشريط المتحرك
    function updateTickerPreview() {
        const messages = document.getElementById('ticker_messages').value;
        const preview = document.getElementById('previewContent');
        
        if (messages.trim()) {
            const messageArray = messages.split('\n').filter(msg => msg.trim());
            preview.innerHTML = messageArray.map(msg => `<span style="margin-left: 50px;">${msg.trim()}</span>`).join('');
        } else {
            preview.innerHTML = '<span style="margin-left: 50px;">لا توجد رسائل</span>';
        }
    }
    
    // تحديث معاينة اسم المركز
    function updateCenterPreview() {
        const centerName = document.getElementById('center_name').value;
        // يمكن إضافة تحديث معاينة اسم المركز هنا
        console.log('Center name updated:', centerName);
    }
    
    // إعداد المستمعين
    document.getElementById('ticker_messages').addEventListener('input', updateTickerPreview);
    document.getElementById('center_name').addEventListener('input', updateCenterPreview);
    
    // تحديث أولي
    updateTickerPreview();
    
    // معالجة إرسال النموذج
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // إظهار رسالة النجاح
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                تم حفظ الإعدادات بنجاح
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.settings-card'));
            
            // إخفاء الرسالة بعد 5 ثوان
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في حفظ الإعدادات');
        });
    });
</script>

<?php include 'includes/admin_footer.php'; ?>