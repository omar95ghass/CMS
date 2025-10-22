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

    // تحميل البيانات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
        loadServices();
        loadUserServices();
    });
</script>

<?php include 'includes/admin_footer.php'; ?>