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
        
        $success_message = 'تم حفظ الإعدادات بنجاح';
        
    } catch (Exception $e) {
        $error_message = 'خطأ في حفظ الإعدادات: ' . htmlspecialchars($e->getMessage());
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
    
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px;
    }
    
    .table td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .service-badge {
        display: inline-block;
        background: linear-gradient(135deg, #28a745 0%, #20bf6b 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 15px;
        font-size: 0.8rem;
        margin: 2px;
        position: relative;
    }
    
    .btn-close-service {
        background: none;
        border: none;
        color: white;
        margin-left: 5px;
        padding: 0;
        font-size: 0.7rem;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .btn-close-service:hover {
        opacity: 1;
    }
    
    .services-container {
        margin-bottom: 5px;
    }
    
    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .text-muted {
        color: #6c757d !important;
        font-size: 0.85rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-icon {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 10px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .btn-action {
        padding: 5px 10px;
        margin: 2px;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    
    .btn-remove {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        border: none;
    }
    
    .btn-remove:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        color: white;
    }
    
    /* أنماط حالة قواعد البيانات */
    .db-status-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .db-status-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .db-status-header i {
        font-size: 1.5rem;
    }
    
    .db-status-header h4 {
        margin: 0;
        font-size: 1.2rem;
    }
    
    .db-status-body {
        padding: 20px;
    }
    
    .status-indicator {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dc3545;
        animation: pulse 2s infinite;
    }
    
    .status-dot.connected {
        background: #28a745;
    }
    
    .status-dot.disconnected {
        background: #dc3545;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .status-text {
        font-weight: 600;
        color: #333;
    }
    
    .db-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    
    .stat-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #666;
        margin-top: 5px;
    }
    
    .sync-controls {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .sync-info h5 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .sync-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .sync-actions .btn {
        width: 100%;
    }
    
    .sync-log {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }
    
    .log-container {
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border-radius: 5px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
    
    .log-entry {
        margin-bottom: 5px;
        padding: 5px;
        border-radius: 3px;
    }
    
    .log-entry.success {
        background: #d4edda;
        color: #155724;
    }
    
    .log-entry.error {
        background: #f8d7da;
        color: #721c24;
    }
    
    .log-entry.warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .active-db {
        border: 3px solid #28a745 !important;
        box-shadow: 0 0 15px rgba(40, 167, 69, 0.3) !important;
    }
    
    .active-db .db-status-header {
        background: linear-gradient(135deg, #28a745 0%, #20bf6b 100%) !important;
    }
    
    /* أنماط رفع اللوغو */
    .logo-upload-container {
        display: flex;
        gap: 20px;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    .current-logo, .logo-upload-area {
        flex: 1;
        text-align: center;
    }
    
    .logo-info {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #666;
    }
    
    .upload-placeholder {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .upload-placeholder:hover {
        border-color: #667eea;
        background: #f0f2ff;
    }
    
    .upload-placeholder i {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 10px;
    }
    
    .upload-placeholder p {
        margin: 10px 0 5px 0;
        font-weight: 600;
        color: #333;
    }
    
    .upload-placeholder small {
        color: #666;
        font-size: 0.8rem;
    }
    
    .logo-preview {
        margin-top: 15px;
        text-align: center;
    }
    
    .logo-actions {
        margin-top: 15px;
        text-align: center;
    }
    
    .btn-save-logo {
        background: linear-gradient(135deg, #28a745 0%, #20bf6b 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        margin-right: 10px;
    }
    
    .btn-cancel-logo {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
    }
</style>

<div class="container">
    <div class="settings-card">
        <div class="settings-header">
            <h2><i class="fas fa-cog"></i> إعدادات النظام</h2>
            <p>إدارة إعدادات المركز والرسائل والطابعة</p>
        </div>
        
        <div class="settings-body">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
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
                
                <div class="form-group">
                    <label class="form-label" for="logo_upload">صورة اللوغو</label>
                    <div class="logo-upload-container">
                        <div class="current-logo">
                            <img id="currentLogoPreview" src="images/logo/logo.png" alt="اللوغو الحالي" style="max-width: 150px; max-height: 100px; border: 2px solid #ddd; border-radius: 8px; padding: 10px;">
                            <p class="logo-info">اللوغو الحالي</p>
                        </div>
                        <div class="logo-upload-area">
                            <input type="file" id="logo_upload" name="logo_upload" accept="image/*" style="display: none;">
                            <div class="upload-placeholder" onclick="document.getElementById('logo_upload').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>انقر لرفع صورة جديدة</p>
                                <small>PNG, JPG, GIF - الحد الأقصى 2MB</small>
                            </div>
                        </div>
                    </div>
                    <div id="logoPreview" class="logo-preview" style="display: none;">
                        <img id="previewImage" src="" alt="معاينة اللوغو" style="max-width: 150px; max-height: 100px; border: 2px solid #28a745; border-radius: 8px; padding: 10px;">
                        <p class="logo-info">معاينة اللوغو الجديد</p>
                    </div>
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

    <!-- قسم حالة قواعد البيانات -->
    <div class="settings-card">
        <div class="settings-header">
            <h2><i class="fas fa-database"></i> حالة قواعد البيانات</h2>
            <p>مراقبة حالة الاتصال والمزامنة بين قاعدتي البيانات</p>
        </div>
        
        <div class="settings-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="db-status-card" id="mysqlStatus">
                        <div class="db-status-header">
                            <i class="fas fa-database"></i>
                            <h4>MySQL</h4>
                        </div>
                        <div class="db-status-body">
                            <div class="status-indicator" id="mysqlIndicator">
                                <span class="status-dot"></span>
                                <span class="status-text">جاري التحقق...</span>
                            </div>
                            <div class="db-stats" id="mysqlStats">
                                <!-- سيتم ملؤها بواسطة JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="db-status-card" id="sqliteStatus">
                        <div class="db-status-header">
                            <i class="fas fa-database"></i>
                            <h4>SQLite</h4>
                        </div>
                        <div class="db-status-body">
                            <div class="status-indicator" id="sqliteIndicator">
                                <span class="status-dot"></span>
                                <span class="status-text">جاري التحقق...</span>
                            </div>
                            <div class="db-stats" id="sqliteStats">
                                <!-- سيتم ملؤها بواسطة JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sync-controls">
                <div class="row">
                    <div class="col-md-8">
                        <div class="sync-info">
                            <h5><i class="fas fa-sync"></i> حالة المزامنة</h5>
                            <p id="syncStatus">جاري التحقق من حالة المزامنة...</p>
                            <small id="lastSyncTime" class="text-muted"></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sync-actions">
                            <button class="btn btn-primary" id="syncNowBtn">
                                <i class="fas fa-sync"></i> مزامنة الآن
                            </button>
                            <button class="btn btn-warning" id="repairDataBtn">
                                <i class="fas fa-tools"></i> إصلاح البيانات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sync-log mt-4">
                <h6><i class="fas fa-list"></i> سجل المزامنة</h6>
                <div class="log-container" id="syncLog">
                    <!-- سيتم ملؤها بواسطة JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- قسم إدارة ربط المستخدمين بالخدمات -->
    <div class="settings-card">
        <div class="settings-header">
            <h2><i class="fas fa-users-cog"></i> إدارة ربط المستخدمين بالخدمات</h2>
            <p>ربط المستخدمين بالخدمات المتاحة</p>
        </div>
        
        <!-- إحصائيات سريعة -->
        <!-- <div class="row mb-4" id="statsRow">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalUsers">0</div>
                        <div class="stat-label">المستخدمين</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalServices">0</div>
                        <div class="stat-label">الخدمات</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalLinks">0</div>
                        <div class="stat-label">الربطات</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="coveragePercent">0%</div>
                        <div class="stat-label">التغطية</div>
                    </div>
                </div>
            </div>
        </div> -->
        
        <div class="settings-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="userSelect">اختر المستخدم</label>
                        <select class="form-control" id="userSelect">
                            <option value="">اختر مستخدم...</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="serviceSelect">اختر الخدمة</label>
                        <select class="form-control" id="serviceSelect">
                            <option value="">اختر خدمة...</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <button type="button" class="btn btn-primary" id="addUserServiceBtn">
                    <i class="fas fa-plus"></i> إضافة ربط
                </button>
                <button type="button" class="btn btn-warning" id="assignAllServicesBtn">
                    <i class="fas fa-check-double"></i> ربط بجميع الخدمات
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped" id="userServicesTable">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الشباك</th>
                            <th>الخدمات المربوطة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </tbody>
                </table>
            </div>
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
    
    // إدارة رفع اللوغو
    function setupLogoUpload() {
        const logoUpload = document.getElementById('logo_upload');
        const logoPreview = document.getElementById('logoPreview');
        const previewImage = document.getElementById('previewImage');
        
        logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // التحقق من نوع الملف
                if (!file.type.startsWith('image/')) {
                    showAlert('يرجى اختيار ملف صورة صحيح', 'error');
                    return;
                }
                
                // التحقق من حجم الملف (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('حجم الملف كبير جداً. الحد الأقصى 2MB', 'error');
                    return;
                }
                
                // عرض معاينة الصورة
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    logoPreview.style.display = 'block';
                    
                    // إضافة أزرار الحفظ والإلغاء
                    if (!document.getElementById('logoActions')) {
                        const logoActions = document.createElement('div');
                        logoActions.id = 'logoActions';
                        logoActions.className = 'logo-actions';
                        logoActions.innerHTML = `
                            <button class="btn btn-save-logo" onclick="saveLogo()">
                                <i class="fas fa-save"></i> حفظ اللوغو
                            </button>
                            <button class="btn btn-cancel-logo" onclick="cancelLogoUpload()">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                        `;
                        logoPreview.appendChild(logoActions);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    function saveLogo() {
        const logoUpload = document.getElementById('logo_upload');
        const file = logoUpload.files[0];
        
        if (!file) {
            showAlert('يرجى اختيار ملف صورة', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('logo_upload', file);
        
        fetch('php/upload_logo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('تم حفظ اللوغو بنجاح', 'success');
                // تحديث معاينة اللوغو الحالي
                document.getElementById('currentLogoPreview').src = data.logo_path + '?t=' + new Date().getTime();
                cancelLogoUpload();
            } else {
                showAlert('فشل في حفظ اللوغو: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('حدث خطأ في حفظ اللوغو', 'error');
        });
    }
    
    function cancelLogoUpload() {
        document.getElementById('logo_upload').value = '';
        document.getElementById('logoPreview').style.display = 'none';
        const logoActions = document.getElementById('logoActions');
        if (logoActions) {
            logoActions.remove();
        }
    }
    
    // إعداد المستمعين
    document.getElementById('ticker_messages').addEventListener('input', updateTickerPreview);
    document.getElementById('center_name').addEventListener('input', updateCenterPreview);
    setupLogoUpload();
    
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

    // إدارة ربط المستخدمين بالخدمات
    let users = [];
    let services = [];
    let userServices = [];

    // تحميل المستخدمين
    function loadUsers() {
        fetch('php/get_users.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    users = data.users.filter(user => user.role === 'counter');
                    const userSelect = document.getElementById('userSelect');
                    userSelect.innerHTML = '<option value="">اختر مستخدم...</option>';
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.username} (شباك ${user.window_number})`;
                        userSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading users:', error));
    }

    // تحميل الخدمات
    function loadServices() {
        fetch('php/get_services.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    services = data.services;
                    const serviceSelect = document.getElementById('serviceSelect');
                    serviceSelect.innerHTML = '<option value="">اختر خدمة...</option>';
                    services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.name;
                        option.textContent = service.name;
                        serviceSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading services:', error));
    }

    // تحميل ربط المستخدمين بالخدمات
    function loadUserServices() {
        fetch('php/get_user_services.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    userServices = data.userServices;
                    updateUserServicesTable();
                    updateStats();
                }
            })
            .catch(error => console.error('Error loading user services:', error));
    }

    // تحديث الإحصائيات
    // function updateStats() {
    //     const totalUsers = users.length;
    //     const totalServices = services.length;
    //     const totalLinks = userServices.length;
    //     const coveragePercent = totalUsers > 0 ? Math.round((totalLinks / (totalUsers * totalServices)) * 100) : 0;

    //     document.getElementById('totalUsers').textContent = totalUsers;
    //     document.getElementById('totalServices').textContent = totalServices;
    //     document.getElementById('totalLinks').textContent = totalLinks;
    //     document.getElementById('coveragePercent').textContent = coveragePercent + '%';
    // }

    // تحديث جدول ربط المستخدمين بالخدمات
    function updateUserServicesTable() {
        const tbody = document.querySelector('#userServicesTable tbody');
        tbody.innerHTML = '';

        // تجميع الخدمات لكل مستخدم
        const userServicesMap = {};
        userServices.forEach(us => {
            if (!userServicesMap[us.user_id]) {
                userServicesMap[us.user_id] = {
                    user: users.find(u => u.id == us.user_id),
                    services: []
                };
            }
            userServicesMap[us.user_id].services.push(us.clinic);
        });

        // عرض البيانات في الجدول
        Object.values(userServicesMap).forEach(userData => {
            console.log(userData);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${userData.user.username}</td>
                <td>${userData.user.window_number}</td>
                <td>
                    <div class="services-container">
                        ${userData.services.length > 0 ? 
                            userData.services.map(service => 
                                `<span class="service-badge">
                                    ${service}
                                    <button class="btn-close-service" onclick="removeSpecificUserService(${userData.user.id}, '${service}')" title="حذف هذه الخدمة">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>`
                            ).join('') :
                            '<span class="text-muted">لا توجد خدمات مربوطة</span>'
                        }
                    </div>
                    <small class="text-muted">(${userData.services.length} خدمة)</small>
                </td>
                <td>
                    <button class="btn btn-action btn-remove" onclick="removeUserServices(${userData.user.id})" ${userData.services.length === 0 ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i> إزالة جميع الخدمات
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // إضافة ربط مستخدم بخدمة
    function addUserService() {
        const userId = document.getElementById('userSelect').value;
        const serviceName = document.getElementById('serviceSelect').value;

        if (!userId || !serviceName) {
            alert('يرجى اختيار المستخدم والخدمة');
            return;
        }

        fetch('php/add_user_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                clinic: serviceName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadUserServices();
                document.getElementById('userSelect').value = '';
                document.getElementById('serviceSelect').value = '';
                showAlert('تم إضافة الربط بنجاح', 'success');
            } else {
                showAlert(data.message || 'حدث خطأ في إضافة الربط', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('حدث خطأ في إضافة الربط', 'error');
        });
    }

    // ربط المستخدم بجميع الخدمات
    function assignAllServices() {
        const userId = document.getElementById('userSelect').value;

        if (!userId) {
            alert('يرجى اختيار المستخدم');
            return;
        }

        if (!confirm('هل أنت متأكد من ربط هذا المستخدم بجميع الخدمات؟')) {
            return;
        }

        fetch('php/assign_all_services.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadUserServices();
                document.getElementById('userSelect').value = '';
                showAlert('تم ربط المستخدم بجميع الخدمات بنجاح', 'success');
            } else {
                showAlert(data.message || 'حدث خطأ في ربط الخدمات', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('حدث خطأ في ربط الخدمات', 'error');
        });
    }

    // إزالة جميع خدمات المستخدم
    function removeUserServices(userId) {
        if (!confirm('هل أنت متأكد من إزالة جميع خدمات هذا المستخدم؟')) {
            return;
        }

        fetch('php/remove_user_services.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadUserServices();
                showAlert('تم إزالة جميع خدمات المستخدم بنجاح', 'success');
            } else {
                showAlert(data.message || 'حدث خطأ في إزالة الخدمات', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('حدث خطأ في إزالة الخدمات', 'error');
        });
    }

    // إزالة خدمة محددة من مستخدم
    function removeSpecificUserService(userId, clinic) {
        if (!confirm(`هل أنت متأكد من إزالة خدمة "${clinic}" من هذا المستخدم؟`)) {
            return;
        }

        fetch('php/remove_specific_user_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                clinic: clinic
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadUserServices();
                showAlert(`تم إزالة خدمة "${clinic}" بنجاح`, 'success');
            } else {
                showAlert(data.message || 'حدث خطأ في إزالة الخدمة', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('حدث خطأ في إزالة الخدمة', 'error');
        });
    }

    // دالة لعرض التنبيهات
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.settings-card'));
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // إعداد المستمعين
    document.getElementById('addUserServiceBtn').addEventListener('click', addUserService);
    document.getElementById('assignAllServicesBtn').addEventListener('click', assignAllServices);

    // إدارة حالة قواعد البيانات
    function loadDatabaseStatus() {
        fetch('php/sync_manager.php?action=status')
            .then(response => response.json())
            .then(data => {
                updateDatabaseStatus(data);
            })
            .catch(error => {
                console.error('Error loading database status:', error);
                showDatabaseError();
            });
    }
    
    function updateDatabaseStatus(status) {
        // تحديث حالة MySQL
        const mysqlIndicator = document.getElementById('mysqlIndicator');
        const mysqlDot = mysqlIndicator.querySelector('.status-dot');
        const mysqlText = mysqlIndicator.querySelector('.status-text');
        
        if (status.mysql_available) {
            mysqlDot.className = 'status-dot connected';
            mysqlText.textContent = 'متصل';
        } else {
            mysqlDot.className = 'status-dot disconnected';
            mysqlText.textContent = 'غير متصل';
        }
        
        // تحديث حالة SQLite
        const sqliteIndicator = document.getElementById('sqliteIndicator');
        const sqliteDot = sqliteIndicator.querySelector('.status-dot');
        const sqliteText = sqliteIndicator.querySelector('.status-text');
        
        if (status.sqlite_available) {
            sqliteDot.className = 'status-dot connected';
            sqliteText.textContent = 'متصل';
        } else {
            sqliteDot.className = 'status-dot disconnected';
            sqliteText.textContent = 'غير متصل';
        }
        
        // تحديث حالة المزامنة
        const syncStatus = document.getElementById('syncStatus');
        const lastSyncTime = document.getElementById('lastSyncTime');
        
        if (status.sync_needed) {
            syncStatus.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> تحتاج إلى مزامنة</span>';
        } else {
            syncStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> متزامنة</span>';
        }
        
        if (status.last_sync_time) {
            lastSyncTime.textContent = 'آخر مزامنة: ' + status.last_sync_time;
        } else {
            lastSyncTime.textContent = 'لم يتم المزامنة بعد';
        }
        
        // تحديث قاعدة البيانات النشطة
        const currentDb = status.current_database;
        if (currentDb === 'mysql') {
            mysqlIndicator.classList.add('active-db');
            sqliteIndicator.classList.remove('active-db');
        } else {
            sqliteIndicator.classList.add('active-db');
            mysqlIndicator.classList.remove('active-db');
        }
    }
    
    function showDatabaseError() {
        const mysqlIndicator = document.getElementById('mysqlIndicator');
        const sqliteIndicator = document.getElementById('sqliteIndicator');
        
        mysqlIndicator.querySelector('.status-dot').className = 'status-dot disconnected';
        mysqlIndicator.querySelector('.status-text').textContent = 'خطأ في الاتصال';
        
        sqliteIndicator.querySelector('.status-dot').className = 'status-dot disconnected';
        sqliteIndicator.querySelector('.status-text').textContent = 'خطأ في الاتصال';
    }
    
    function performSync() {
        const syncBtn = document.getElementById('syncNowBtn');
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري المزامنة...';
        
        fetch('php/sync_manager.php?action=sync')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    addLogEntry('تمت المزامنة بنجاح', 'success');
                    loadDatabaseStatus();
                } else {
                    addLogEntry('فشلت المزامنة: ' + data.message, 'error');
                }
            })
            .catch(error => {
                addLogEntry('خطأ في المزامنة: ' + error.message, 'error');
            })
            .finally(() => {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="fas fa-sync"></i> مزامنة الآن';
            });
    }
    
    function repairData() {
        const repairBtn = document.getElementById('repairDataBtn');
        repairBtn.disabled = true;
        repairBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإصلاح...';
        
        fetch('php/sync_manager.php?action=repair')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    addLogEntry('تم إصلاح البيانات بنجاح', 'success');
                    loadDatabaseStatus();
                } else {
                    addLogEntry('فشل إصلاح البيانات: ' + data.message, 'error');
                }
            })
            .catch(error => {
                addLogEntry('خطأ في إصلاح البيانات: ' + error.message, 'error');
            })
            .finally(() => {
                repairBtn.disabled = false;
                repairBtn.innerHTML = '<i class="fas fa-tools"></i> إصلاح البيانات';
            });
    }
    
    function addLogEntry(message, type) {
        const logContainer = document.getElementById('syncLog');
        const entry = document.createElement('div');
        entry.className = `log-entry ${type}`;
        entry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
        
        logContainer.insertBefore(entry, logContainer.firstChild);
        
        // الاحتفاظ بآخر 50 إدخال فقط
        while (logContainer.children.length > 50) {
            logContainer.removeChild(logContainer.lastChild);
        }
    }
    
    function loadDatabaseStatistics() {
        fetch('php/sync_manager.php?action=statistics')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateDatabaseStatistics(data.statistics);
                }
            })
            .catch(error => {
                console.error('Error loading database statistics:', error);
            });
    }
    
    function updateDatabaseStatistics(stats) {
        // تحديث إحصائيات MySQL
        if (stats.mysql) {
            const mysqlStats = document.getElementById('mysqlStats');
            mysqlStats.innerHTML = `
                <div class="stat-item">
                    <div class="stat-number">${stats.mysql.screens_count}</div>
                    <div class="stat-label">الشاشات</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.mysql.users_count}</div>
                    <div class="stat-label">المستخدمين</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.mysql.queue_count}</div>
                    <div class="stat-label">الأدوار</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.mysql.settings_count}</div>
                    <div class="stat-label">الإعدادات</div>
                </div>
            `;
        }
        
        // تحديث إحصائيات SQLite
        if (stats.sqlite) {
            const sqliteStats = document.getElementById('sqliteStats');
            sqliteStats.innerHTML = `
                <div class="stat-item">
                    <div class="stat-number">${stats.sqlite.screens_count}</div>
                    <div class="stat-label">الشاشات</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.sqlite.users_count}</div>
                    <div class="stat-label">المستخدمين</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.sqlite.queue_count}</div>
                    <div class="stat-label">الأدوار</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${stats.sqlite.settings_count}</div>
                    <div class="stat-label">الإعدادات</div>
                </div>
            `;
        }
    }
    
    // إعداد المستمعين
    document.getElementById('syncNowBtn').addEventListener('click', performSync);
    document.getElementById('repairDataBtn').addEventListener('click', repairData);
    
    // تحديث البيانات كل 30 ثانية
    setInterval(() => {
        loadDatabaseStatus();
        loadDatabaseStatistics();
    }, 30000);

    // تحميل البيانات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
        loadServices();
        loadUserServices();
        loadDatabaseStatus();
        loadDatabaseStatistics();
    });
</script>

<?php include 'includes/admin_footer.php'; ?>