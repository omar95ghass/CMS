<?php
    session_start();
    
    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $winNum = $_SESSION['windowNumber'];
    $userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الطوابير - النافذة <?php echo $winNum; ?></title>
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        
        /* ألوان رئيسية */
        :root {
            --dark-blue: #003366;
            --medium-blue: #0056a2;
            --light-blue: #007bff;
            --orange: #ff7f00;
            --white: #ffffff;
            --grey-text: #ccc;
            --light-grey-bg: #f5f5f5;
            --green-tag: #28a745;
            --yellow-tag: #ffc107;
            --blue-tag: #17a2b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            direction: rtl;
            font-family: 'Cairo', sans-serif;
        }

        /* محاكاة الإطار اللوحي */
        .tablet-frame {
            width: 900px;
            height: 600px;
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            height: 100%;
            background-color: var(--white);
        }

        /* شريط علوي */
        .top-bar {
            background-color: var(--medium-blue);
            color: var(--white);
            padding: 5px 15px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .notifications {
            font-weight: bold;
        }
        .notifications .icon {
            margin-right: 5px;
        }

        /* التخطيط الرئيسي للأعمدة */
        .main-content {
            display: flex;
            flex: 1;
        }

        .column {
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 15px;
        }

        /* 1. العمود الأيسر (رقم التذكرة) */
        .left-panel {
            flex: 2;
            background-color: var(--white);
            text-align: center;
            padding: 30px 15px 15px 15px;
        }

        .info-card {
            padding: 20px;
        }

        .label, .label-sm {
            color: var(--dark-blue);
            font-size: 1.2rem;
            font-weight: 400;
        }

        .label-lg {
            color: var(--orange);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .token-number {
            font-size: 8rem;
            font-weight: 700;
            color: var(--orange);
            border: 5px solid var(--orange);
            border-radius: 10px;
            padding: 10px 0;
            margin: 20px auto;
            width: 70%;
        }

        .serving-time {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 40px;
        }

        .performance-footer {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--dark-blue);
            border-top: 1px solid var(--grey-text);
            padding-top: 15px;
            margin-top: auto;
        }
        .excellent {
            font-weight: bold;
            color: var(--green-tag);
        }

        /* 2. العمود الأوسط (الأزرار) */
        .center-panel {
            flex: 1;
            background-color: var(--dark-blue);
            gap: 10px;
            padding: 15px 20px;
        }

        .action-btn {
            background-color: var(--medium-blue);
            color: var(--white);
            border: none;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            transition: background-color 0.3s;
            margin-bottom: 10px;
        }

        .action-btn:hover {
            background-color: var(--light-blue);
        }

        .action-btn.success {
            background-color: var(--green-tag);
        }

        .action-btn.warning {
            background-color: var(--yellow-tag);
            color: #333;
        }

        .action-btn.danger {
            background-color: #dc3545;
        }

        /* 3. العمود الأيمن (قائمة الزوار) */
        .right-panel {
            flex: 1.2;
            background-color: var(--light-grey-bg);
            padding: 0;
        }

        .department-header {
            background-color: var(--dark-blue);
            color: var(--white);
            padding: 10px 15px;
            font-weight: 700;
            font-size: 1rem;
            text-align: center;
        }

        .visitor-list-container {
            overflow-y: auto;
            flex: 1;
            max-height: 400px;
        }

        .visitor-item {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            background-color: var(--white);
            transition: background-color 0.3s;
        }

        .visitor-item:hover {
            background-color: #f0f0f0;
        }

        .visitor-item.current {
            background-color: #e3f2fd;
            border-left: 4px solid var(--orange);
        }

        .visitor-name {
            font-weight: bold;
            color: var(--dark-blue);
            flex-basis: 70%;
        }

        .visitor-detail {
            flex-basis: 100%;
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .wait-time {
            font-size: 0.8rem;
            color: #999;
            align-self: flex-start;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .status-waiting {
            background-color: #ffc107;
            color: #333;
        }

        .status-called {
            background-color: #17a2b8;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        /* زر إضافة زائر في الأسفل */
        .add-visitor-btn {
            background-color: var(--orange);
            color: var(--white);
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: auto;
            width: 100%;
            transition: background-color 0.3s;
        }

        .add-visitor-btn:hover {
            background-color: #e66900;
        }

        /* رسائل التنبيه */
        .alert-box {
            position: fixed;
            top: 20px;
            right: 50%;
            transform: translateX(50%);
            min-width: 250px;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            z-index: 1000;
            display: none;
            font-size: 16px;
        }
        .alert-success { background: #28a745; color: #fff; }
        .alert-warning { background: #ffc107; color: #000; }
        .alert-error   { background: #dc3545; color: #fff; }

        /* نموذج إدخال رقم محدد */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }

        .modal input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal button {
            background-color: var(--medium-blue);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .modal button:hover {
            background-color: var(--light-blue);
        }
    </style>
</head>
<body>
    <div class="tablet-frame">
        <div class="app-container">
            <div class="top-bar">
                <span class="user-info">النافذة <?php echo $winNum; ?> | <?php echo date('Y-m-d H:i:s'); ?></span>
                <span class="notifications">
                    <span class="icon">🔔</span> <span id="waitingCount">0</span> زائر في الانتظار
                </span>
            </div>

            <div class="main-content">
                <div class="column left-panel">
                    <div class="info-card serving-status">
                        <p class="label">الخدمة الحالية</p>
                        <h2 class="label-lg">رقم الدور</h2>
                        <div class="token-number" id="currentNumber">--</div>
                        <p class="label-sm">وقت الخدمة</p>
                        <div class="serving-time" id="servingTime">00:00:00</div>
                    </div>
                    <div class="performance-footer">
                        <span class="stat">إجمالي الأدوار المكتملة: <span id="completedCount">0</span></span>
                        <span class="stat">الحالة: <span class="excellent" id="performanceStatus">ممتاز</span></span>
                    </div>
                </div>

                <div class="column center-panel">
                    <button class="action-btn success" id="callNext">التالي</button>
                    <button class="action-btn" id="callSpecific">نداء محدد</button>
                    <button class="action-btn warning" id="recallBtn">إعادة نداء</button>
                    <button class="action-btn" id="startService">بدء الخدمة</button>
                    <button class="action-btn danger" id="completeService">إنهاء الخدمة</button>
                    <button class="action-btn danger" id="logoutBtn">تسجيل الخروج</button>
                </div>

                <div class="column right-panel">
                    <div class="department-header">قائمة الأدوار</div>
                    <div class="visitor-list-container" id="queueList">
                        <!-- قائمة الأدوار ستأتي هنا من JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نموذج إدخال رقم محدد -->
    <div id="specificModal" class="modal">
        <div class="modal-content">
            <h3>نداء على دور محدد</h3>
            <input type="number" id="specificNumber" placeholder="أدخل رقم الدور">
            <div>
                <button onclick="callSpecificNumber()">نداء</button>
                <button onclick="closeModal()">إلغاء</button>
            </div>
        </div>
    </div>

    <!-- صندوق التنبيه -->
    <div id="alertBox" class="alert-box"></div>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script>
        let currentServingNumber = null;
        let serviceStartTime = null;
        let servingInterval = null;
        let queueData = [];

        // تحديث الوقت
        function updateTime() {
            const now = new Date();
            document.querySelector('.user-info').innerHTML = 
                `النافذة <?php echo $winNum; ?> | ${now.toLocaleString('ar-SA')}`;
        }
        setInterval(updateTime, 1000);

        // تحديث قائمة الأدوار
        function updateQueue() {
            fetch('php/get_queue.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        queueData = data.data;
                        displayQueue(data.data);
                        updateStats(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching queue:', error);
                    showAlert('خطأ في جلب قائمة الأدوار', 'error');
                });
        }

        // عرض قائمة الأدوار
        function displayQueue(queue) {
            const container = document.getElementById('queueList');
            container.innerHTML = '';

            if (queue.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">لا توجد أدوار في الانتظار</div>';
                return;
            }

            queue.forEach(item => {
                const div = document.createElement('div');
                div.className = 'visitor-item';
                if (item.status === 'called') {
                    div.classList.add('current');
                }

                const statusText = {
                    'waiting': 'في الانتظار',
                    'called': 'مدعو',
                    'completed': 'مكتمل',
                    'announced': 'تم الإعلان'
                };

                const statusClass = {
                    'waiting': 'status-waiting',
                    'called': 'status-called',
                    'completed': 'status-completed',
                    'announced': 'status-announced'
                };

                // إضافة كلاس للحالة المكتملة
                if (item.status === 'completed') {
                    div.style.opacity = '0.6';
                    div.style.textDecoration = 'line-through';
                }

                div.innerHTML = `
                    <p class="visitor-name">رقم ${item.number}</p>
                    <p class="visitor-detail">
                        <span class="detail-number">${item.clinic}</span>
                        <div class="tag-container">
                            <span class="status-badge ${statusClass[item.status] || 'status-waiting'}">${statusText[item.status] || item.status}</span>
                        </div>
                    </p>
                    <span class="wait-time">${formatTime(item.created_at)}</span>
                `;
                container.appendChild(div);
            });
        }

        // تحديث الإحصائيات
        function updateStats(queue) {
            const waiting = queue.filter(item => item.status === 'waiting').length;
            const completed = queue.filter(item => item.status === 'completed').length;
            
            document.getElementById('waitingCount').textContent = waiting;
            document.getElementById('completedCount').textContent = completed;
        }

        // تنسيق الوقت
        function formatTime(timestamp) {
            const now = new Date();
            const created = new Date(timestamp);
            const diff = Math.floor((now - created) / 60000); // بالدقائق
            
            if (diff < 60) {
                return `${diff} دقيقة`;
            } else {
                const hours = Math.floor(diff / 60);
                const minutes = diff % 60;
                return `${hours}س ${minutes}د`;
            }
        }

        // بدء عداد وقت الخدمة
        function startServiceTimer() {
            serviceStartTime = new Date();
            servingInterval = setInterval(() => {
                if (serviceStartTime) {
                    const now = new Date();
                    const diff = Math.floor((now - serviceStartTime) / 1000);
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;
                    document.getElementById('servingTime').textContent = 
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        // إيقاف عداد وقت الخدمة
        function stopServiceTimer() {
            if (servingInterval) {
                clearInterval(servingInterval);
                servingInterval = null;
            }
            serviceStartTime = null;
            document.getElementById('servingTime').textContent = '00:00:00';
        }

        // نداء على الدور التالي
        document.getElementById('callNext').addEventListener('click', function() {
            fetch('php/call_next.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentServingNumber = data.number;
                    document.getElementById('currentNumber').textContent = data.number;
                    showAlert(`تم نداء الدور رقم ${data.number}`, 'success');
                    updateQueue();
                } else {
                    showAlert(data.message || 'لا توجد أدوار في الانتظار', 'warning');
                }
            })
            .catch(error => {
                console.error('Error calling next:', error);
                showAlert('خطأ في نداء الدور التالي', 'error');
            });
        });

        // فتح نموذج النداء المحدد
        document.getElementById('callSpecific').addEventListener('click', function() {
            document.getElementById('specificModal').style.display = 'block';
            document.getElementById('specificNumber').focus();
        });

        // نداء على دور محدد
        function callSpecificNumber() {
            const number = document.getElementById('specificNumber').value;
            if (!number) {
                showAlert('يرجى إدخال رقم الدور', 'warning');
                return;
            }

            fetch('php/call_specific.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ number: parseInt(number) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentServingNumber = parseInt(number);
                    document.getElementById('currentNumber').textContent = number;
                    showAlert(`تم نداء الدور رقم ${number}`, 'success');
                    closeModal();
                    updateQueue();
                } else {
                    showAlert(data.message || 'رقم الدور غير موجود', 'error');
                }
            })
            .catch(error => {
                console.error('Error calling specific:', error);
                showAlert('خطأ في نداء الدور المحدد', 'error');
            });
        }

        // إغلاق النموذج
        function closeModal() {
            document.getElementById('specificModal').style.display = 'none';
            document.getElementById('specificNumber').value = '';
        }

        // بدء الخدمة
        document.getElementById('startService').addEventListener('click', function() {
            if (currentServingNumber) {
                startServiceTimer();
                showAlert('تم بدء الخدمة', 'success');
            } else {
                showAlert('يرجى نداء دور أولاً', 'warning');
            }
        });

        // إنهاء الخدمة
        document.getElementById('completeService').addEventListener('click', function() {
            if (currentServingNumber) {
                completeService(currentServingNumber);
            } else {
                showAlert('لا يوجد دور قيد الخدمة', 'warning');
            }
        });

        // إعادة النداء
        document.getElementById('recallBtn').addEventListener('click', function() {
            if (currentServingNumber) {
                showAlert(`إعادة نداء الدور رقم ${currentServingNumber}`, 'success');
            } else {
                showAlert('لا يوجد دور قيد الخدمة', 'warning');
            }
        });

        // دالة إنهاء الخدمة
        function completeService(number) {
            fetch('php/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    number: number, 
                    status: 'completed',
                    date: new Date().toISOString().split('T')[0]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    stopServiceTimer();
                    currentServingNumber = null;
                    document.getElementById('currentNumber').textContent = '--';
                    showAlert('تم إنهاء الخدمة بنجاح', 'success');
                    updateQueue();
                } else {
                    showAlert('فشل في إنهاء الخدمة: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error completing service:', error);
                showAlert('خطأ في إنهاء الخدمة', 'error');
            });
        }

        // تسجيل الخروج
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من تسجيل الخروج؟')) {
                window.location.href = 'php/logout_function.php';
            }
        });

        // إغلاق النموذج عند النقر خارجه
        window.onclick = function(event) {
            const modal = document.getElementById('specificModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // عرض التنبيهات
        function showAlert(message, type) {
            const box = document.getElementById("alertBox");
            box.className = `alert-box alert-${type}`;
            box.innerHTML = message;
            box.style.display = "block";
            setTimeout(() => { box.style.display = "none"; }, 4000);
        }

        // تحديث البيانات كل 5 ثوان
        setInterval(updateQueue, 5000);
        
        // تحديث أولي
        updateQueue();
    </script>
</body>
</html>
