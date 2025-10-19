<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار إعادة النداء</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>اختبار إعادة النداء - النسخة النهائية</h1>
    
    <div class="test-section info">
        <h3>تعليمات الاختبار:</h3>
        <ol>
            <li>اضغط على "إنشاء دور للاختبار"</li>
            <li>اضغط على "نداء الدور"</li>
            <li>اضغط على "إعادة النداء" عدة مرات</li>
            <li>تحقق من أن الصوت يظهر في شاشة العرض</li>
        </ol>
    </div>

    <div class="test-section">
        <h3>1. إنشاء دور للاختبار</h3>
        <button onclick="createTestQueue()">إنشاء دور للاختبار</button>
        <div id="createResult"></div>
    </div>

    <div class="test-section">
        <h3>2. نداء الدور</h3>
        <button onclick="callNext()">نداء الدور</button>
        <div id="callResult"></div>
    </div>

    <div class="test-section">
        <h3>3. إعادة النداء</h3>
        <button onclick="recallQueue()">إعادة النداء</button>
        <button onclick="recallQueue()">إعادة النداء مرة أخرى</button>
        <button onclick="recallQueue()">إعادة النداء مرة ثالثة</button>
        <div id="recallResult"></div>
    </div>

    <div class="test-section">
        <h3>4. فحص get_pending_calls</h3>
        <button onclick="checkPendingCalls()">فحص النداءات المعلقة</button>
        <div id="pendingResult"></div>
    </div>

    <div class="test-section">
        <h3>5. فحص قاعدة البيانات</h3>
        <button onclick="checkDatabase()">فحص قاعدة البيانات</button>
        <div id="dbResult"></div>
    </div>

    <script>
        let currentNumber = null;
        let currentClinic = null;

        function createTestQueue() {
            fetch('test_create_queue.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('createResult').innerHTML = '<pre>' + data + '</pre>';
                })
                .catch(error => {
                    document.getElementById('createResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                });
        }

        function callNext() {
            fetch('php/call_next.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentNumber = data.number;
                    currentClinic = data.clinic;
                    document.getElementById('callResult').innerHTML = 
                        '<div class="success">تم نداء الدور رقم ' + data.number + ' للخدمة ' + data.clinic + '</div>';
                } else {
                    document.getElementById('callResult').innerHTML = 
                        '<div class="error">فشل في نداء الدور: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('callResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
            });
        }

        function recallQueue() {
            if (!currentNumber || !currentClinic) {
                document.getElementById('recallResult').innerHTML = 
                    '<div class="error">يرجى نداء دور أولاً</div>';
                return;
            }

            fetch('php/recall_queue.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    number: currentNumber,
                    clinic: currentClinic 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('recallResult').innerHTML = 
                        '<div class="success">تم إعادة نداء الدور رقم ' + currentNumber + ' بنجاح</div>';
                } else {
                    document.getElementById('recallResult').innerHTML = 
                        '<div class="error">فشل في إعادة النداء: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('recallResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
            });
        }

        function checkPendingCalls() {
            fetch('php/get_pending_calls.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pendingResult').innerHTML = 
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    document.getElementById('pendingResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                });
        }

        function checkDatabase() {
            fetch('test_check_db.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('dbResult').innerHTML = '<pre>' + data + '</pre>';
                })
                .catch(error => {
                    document.getElementById('dbResult').innerHTML = '<div class="error">خطأ: ' + error + '</div>';
                });
        }
    </script>
</body>
</html>