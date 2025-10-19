<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار نظام النداء المستمر</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background-color: #d4edda; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .info { background-color: #d1ecf1; border-left: 4px solid #17a2b8; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        button { padding: 12px 24px; margin: 8px; cursor: pointer; border: none; border-radius: 4px; font-size: 16px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #e9ecef; }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-left: 8px; }
        .status-active { background-color: #28a745; }
        .status-inactive { background-color: #dc3545; }
        .log-container { max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <h1>اختبار نظام النداء المستمر</h1>
        
        <div class="test-section info">
            <h3>📋 تعليمات الاختبار</h3>
            <ol>
                <li><strong>إنشاء دور:</strong> اضغط على "إنشاء دور للاختبار"</li>
                <li><strong>نداء الدور:</strong> اضغط على "نداء الدور" - سيبدأ النداء المستمر</li>
                <li><strong>مراقبة النداء:</strong> راقب شاشة العرض - يجب أن يعيد النداء كل 20 ثانية</li>
                <li><strong>بدء الخدمة:</strong> اضغط على "بدء الخدمة" - يجب أن يتوقف النداء المستمر</li>
                <li><strong>إنهاء الخدمة:</strong> اضغط على "إنهاء الخدمة"</li>
            </ol>
        </div>

        <div class="test-section">
            <h3>🔧 إعداد الاختبار</h3>
            <button class="btn-primary" onclick="createTestQueue()">إنشاء دور للاختبار</button>
            <button class="btn-info" onclick="checkDatabase()">فحص قاعدة البيانات</button>
            <div id="setupResult"></div>
        </div>

        <div class="test-section">
            <h3>📞 اختبار النداء</h3>
            <button class="btn-success" onclick="callNext()">نداء الدور</button>
            <button class="btn-warning" onclick="recallQueue()">إعادة النداء</button>
            <div id="callResult"></div>
        </div>

        <div class="test-section">
            <h3>⚙️ اختبار الخدمة</h3>
            <button class="btn-info" onclick="startService()">بدء الخدمة</button>
            <button class="btn-danger" onclick="completeService()">إنهاء الخدمة</button>
            <div id="serviceResult"></div>
        </div>

        <div class="test-section">
            <h3>📊 مراقبة النظام</h3>
            <button class="btn-primary" onclick="checkPendingCalls()">فحص النداءات المعلقة</button>
            <button class="btn-info" onclick="checkContinuousAnnouncements()">فحص النداءات المستمرة</button>
            <div id="monitoringResult"></div>
        </div>

        <div class="test-section">
            <h3>📝 سجل الأحداث</h3>
            <button class="btn-warning" onclick="clearLog()">مسح السجل</button>
            <div id="logContainer" class="log-container">
                <div>سجل الأحداث سيظهر هنا...</div>
            </div>
        </div>
    </div>

    <script>
        let currentNumber = null;
        let currentClinic = null;
        let logContainer = document.getElementById('logContainer');

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.innerHTML = `<strong>[${timestamp}]</strong> ${message}`;
            logEntry.style.color = type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#17a2b8';
            logContainer.appendChild(logEntry);
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        function clearLog() {
            logContainer.innerHTML = '<div>تم مسح السجل</div>';
        }

        function createTestQueue() {
            log('بدء إنشاء دور للاختبار...', 'info');
            fetch('test_create_queue.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('setupResult').innerHTML = '<pre>' + data + '</pre>';
                    log('تم إنشاء دور للاختبار بنجاح', 'success');
                })
                .catch(error => {
                    document.getElementById('setupResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                    log('فشل في إنشاء دور للاختبار: ' + error, 'error');
                });
        }

        function callNext() {
            log('بدء نداء الدور...', 'info');
            fetch('php/call_next.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentNumber = data.number;
                    currentClinic = data.clinic;
                    document.getElementById('callResult').innerHTML = 
                        '<div class="success">تم نداء الدور رقم ' + data.number + ' للخدمة ' + data.clinic + '</div>';
                    log('تم نداء الدور رقم ' + data.number + ' - سيبدأ النداء المستمر', 'success');
                } else {
                    document.getElementById('callResult').innerHTML = 
                        '<div class="error">فشل في نداء الدور: ' + data.message + '</div>';
                    log('فشل في نداء الدور: ' + data.message, 'error');
                }
            })
            .catch(error => {
                document.getElementById('callResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                log('خطأ في نداء الدور: ' + error, 'error');
            });
        }

        function recallQueue() {
            if (!currentNumber || !currentClinic) {
                document.getElementById('callResult').innerHTML = 
                    '<div class="warning">يرجى نداء دور أولاً</div>';
                log('لا يمكن إعادة النداء - لم يتم نداء دور بعد', 'warning');
                return;
            }

            log('بدء إعادة النداء...', 'info');
            fetch('php/recall_queue.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ number: currentNumber, clinic: currentClinic })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('callResult').innerHTML = 
                        '<div class="success">تم إعادة نداء الدور رقم ' + currentNumber + ' بنجاح</div>';
                    log('تم إعادة نداء الدور رقم ' + currentNumber, 'success');
                } else {
                    document.getElementById('callResult').innerHTML = 
                        '<div class="error">فشل في إعادة النداء: ' + data.message + '</div>';
                    log('فشل في إعادة النداء: ' + data.message, 'error');
                }
            })
            .catch(error => {
                document.getElementById('callResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                log('خطأ في إعادة النداء: ' + error, 'error');
            });
        }

        function startService() {
            if (!currentNumber || !currentClinic) {
                document.getElementById('serviceResult').innerHTML = 
                    '<div class="warning">يرجى نداء دور أولاً</div>';
                log('لا يمكن بدء الخدمة - لم يتم نداء دور بعد', 'warning');
                return;
            }

            log('بدء الخدمة للدور رقم ' + currentNumber + '...', 'info');
            fetch('php/start_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ number: currentNumber, clinic: currentClinic })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('serviceResult').innerHTML = 
                        '<div class="success">تم بدء الخدمة للدور رقم ' + currentNumber + ' بنجاح</div>';
                    log('تم بدء الخدمة للدور رقم ' + currentNumber + ' - توقف النداء المستمر', 'success');
                } else {
                    document.getElementById('serviceResult').innerHTML = 
                        '<div class="error">فشل في بدء الخدمة: ' + data.message + '</div>';
                    log('فشل في بدء الخدمة: ' + data.message, 'error');
                }
            })
            .catch(error => {
                document.getElementById('serviceResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                log('خطأ في بدء الخدمة: ' + error, 'error');
            });
        }

        function completeService() {
            if (!currentNumber) {
                document.getElementById('serviceResult').innerHTML = 
                    '<div class="warning">لا يوجد دور قيد الخدمة</div>';
                log('لا يمكن إنهاء الخدمة - لا يوجد دور قيد الخدمة', 'warning');
                return;
            }

            log('إنهاء الخدمة للدور رقم ' + currentNumber + '...', 'info');
            fetch('php/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    number: currentNumber, 
                    status: 'completed',
                    date: new Date().toISOString().split('T')[0]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('serviceResult').innerHTML = 
                        '<div class="success">تم إنهاء الخدمة للدور رقم ' + currentNumber + ' بنجاح</div>';
                    log('تم إنهاء الخدمة للدور رقم ' + currentNumber, 'success');
                    currentNumber = null;
                    currentClinic = null;
                } else {
                    document.getElementById('serviceResult').innerHTML = 
                        '<div class="error">فشل في إنهاء الخدمة: ' + data.message + '</div>';
                    log('فشل في إنهاء الخدمة: ' + data.message, 'error');
                }
            })
            .catch(error => {
                document.getElementById('serviceResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                log('خطأ في إنهاء الخدمة: ' + error, 'error');
            });
        }

        function checkPendingCalls() {
            log('فحص النداءات المعلقة...', 'info');
            fetch('php/get_pending_calls.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('monitoringResult').innerHTML = 
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    log('تم فحص النداءات المعلقة - العدد: ' + (data.calls ? data.calls.length : 0), 'info');
                })
                .catch(error => {
                    document.getElementById('monitoringResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                    log('خطأ في فحص النداءات المعلقة: ' + error, 'error');
                });
        }

        function checkContinuousAnnouncements() {
            log('فحص النداءات المستمرة...', 'info');
            // هذه الدالة ستتحقق من النداءات المستمرة في display.php
            document.getElementById('monitoringResult').innerHTML = 
                '<div class="info">تحقق من شاشة العرض لمراقبة النداءات المستمرة</div>';
            log('تحقق من شاشة العرض لمراقبة النداءات المستمرة', 'info');
        }

        function checkDatabase() {
            log('فحص قاعدة البيانات...', 'info');
            fetch('test_check_db.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('setupResult').innerHTML = '<pre>' + data + '</pre>';
                    log('تم فحص قاعدة البيانات', 'info');
                })
                .catch(error => {
                    document.getElementById('setupResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                    log('خطأ في فحص قاعدة البيانات: ' + error, 'error');
                });
        }

        // بدء الاختبار التلقائي
        log('تم تحميل صفحة الاختبار', 'success');
        log('افتح شاشة العرض في نافذة منفصلة لمراقبة النداءات المستمرة', 'info');
    </script>
</body>
</html>