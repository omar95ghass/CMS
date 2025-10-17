<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الأدوار - مركز خدمة المواطن</title>
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header-section {
            background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .header-section p {
            font-size: 1.2rem;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .content-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .display-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 1200px;
            text-align: center;
        }
        
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .window-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 3px solid #1f3c88;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .window-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(31, 60, 136, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .window-card:hover::before {
            left: 100%;
        }
        
        .window-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(31, 60, 136, 0.2);
        }
        
        .window-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f3c88;
            margin-bottom: 15px;
        }
        
        .queue-number {
            font-size: 4rem;
            font-weight: bold;
            color: #28a745;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 10px;
        }
        
        .status-active {
            background: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
        
        .status-waiting {
            background: #ffc107;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        }
        
        .status-inactive {
            background: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }
        
        .welcome-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .welcome-screen h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .welcome-screen p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .start-button {
            background: white;
            color: #667eea;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .start-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .footer-section {
            background: white;
            border-top: 1px solid #ddd;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .ticker {
            width: 100%;
            background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
            color: #fff;
            overflow: hidden;
            position: relative;
            height: 50px;
            display: flex;
            align-items: center;
        }
        
        .ticker-content {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 30s linear infinite;
        }
        
        .ticker-content span {
            display: inline-block;
            margin-left: 100px;
            font-size: 16px;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        @keyframes ticker {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .no-data {
            font-size: 1.5rem;
            color: #6c757d;
            padding: 60px 20px;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: #dc3545;
            padding: 40px 20px;
        }
        
        /* تحسينات الاستجابة */
        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 2rem;
            }
            
            .windows-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 20px;
            }
            
            .queue-number {
                font-size: 3rem;
            }
            
            .welcome-screen h2 {
                font-size: 2rem;
            }
        }
        
        /* تأثيرات الحركة */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- شاشة الترحيب -->
        <div id="welcomeScreen" class="welcome-screen">
            <h2>مرحباً بكم</h2>
            <p>مركز خدمة المواطن - النافذة الواحدة</p>
            <button id="startButton" class="start-button">ابدأ العرض</button>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="header-section">
            <h1>مركز خدمة المواطن</h1>
            <p>النافذة الواحدة - عرض الأدوار الحالية</p>
        </div>
        
        <div class="content-section">
            <div class="display-card">
                <div id="windowsContainer" class="windows-grid">
                    <!-- ستتم إضافة النوافذ هنا ديناميكياً -->
                </div>
                <div id="noDataMessage" class="no-data" style="display: none;">
                    لا توجد أدوار حالياً
                </div>
                <div id="errorMessage" class="error-message" style="display: none;">
                    خطأ في الاتصال بالخادم
                </div>
            </div>
        </div>
        
        <div class="footer-section">
            <div class="ticker">
                <div class="ticker-content">
                    <span>✅ مركز خدمة المواطن - النافذة الواحدة</span>
                    <span>⚠️ يرجى الاحتفاظ بتذكرة الدور</span>
                    <span>📢 لا تخرج من الصالة لتتمكن من سماع النداء</span>
                    <span>🕐 أوقات العمل: من 8:00 صباحاً إلى 4:00 مساءً</span>
                    <span>💡 نتمنى لكم يوماً طيباً</span>
                </div>
            </div>
        </div>
    </div>
    <script>
        let playbackQueue = [];
        let isPlaying = false;
        let isAppStarted = false;
        let lastProcessedIds = new Set(); // لتتبع النداءات المعالجة مسبقاً

        function playNumber(number, windowNumber, onComplete) {
            let q = [];
            function e(filename) { q.push(filename); }

            e('number');
            
            if (number >= 1 && number <= 19) {
                e(number);
            } else {
                let h = Math.floor(number / 100) * 100,
                    t = Math.floor((number % 100) / 10) * 10,
                    o = number % 10;
                if (h) { e(h); if (t || o) e('and'); }
                if (o) { e(o); if (t) e('and'); }
                if (t) e(t);
            }
            e('goto');
            e(windowNumber);

            function next() {
                if (q.length === 0) return onComplete && onComplete();
                let a = new Audio();
                let filename = q.shift();
                
                a.src = 'audio/ar/' + filename + '.mp3';
                a.onended = next;
                a.play().catch(error => {
                    console.error('Error playing audio:', error);
                    next();
                });
            }
            next();
        }

        // علامة Announce بعد النطق
        function markAnnounced(id) {
            fetch('php/mark_announced.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            }).catch(console.error);
        }

        // جلب النداءات الجديدة فقط
        function fetchPendingCalls() {
            if (!isAppStarted) return;
            
            fetch('php/get_pending_calls.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        // تصفية النداءات الجديدة فقط التي لم يتم معالجتها
                        const newCalls = data.calls.filter(call => 
                            !lastProcessedIds.has(call.id) && 
                            (call.status === 'called' || call.status === 'announced')
                        );
                        
                        newCalls.forEach(call => {
                            playbackQueue.push(call);
                            lastProcessedIds.add(call.id);
                        });
                        
                        // تشغيل النداءات فقط إذا لم يكن هناك نداء قيد التشغيل
                        if (!isPlaying) {
                            playFromQueue();
                        }
                    }
                })
                .catch(console.error);
        }

        function playFromQueue() {
            if (isPlaying || !playbackQueue.length) return;
            const item = playbackQueue.shift();
            isPlaying = true;
            playNumber(item.number, item.window_number, () => {
                isPlaying = false;
                markAnnounced(item.id);
                // نطق التالي بعد 2 ثواني
                setTimeout(playFromQueue, 2000);
            });
        }

        function fetchAndDisplayWindows() {
            fetch('php/get_current_number.php')
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('windowsContainer');
                    const noDataMessage = document.getElementById('noDataMessage');
                    const errorMessage = document.getElementById('errorMessage');
                    
                    // إخفاء رسائل الخطأ
                    noDataMessage.style.display = 'none';
                    errorMessage.style.display = 'none';

                    if (data.status === 'success' && data.calls.length > 0) {
                        // ترتيب حسب رقم الشباك
                        data.calls.sort((a, b) => a.window_number - b.window_number);
                        
                        container.innerHTML = '';
                        data.calls.forEach((item, index) => {
                            const windowCard = createWindowCard(item, index);
                            container.appendChild(windowCard);
                        });
                    } else {
                        container.innerHTML = '';
                        noDataMessage.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error fetching windows data:', err);
                    document.getElementById('errorMessage').style.display = 'block';
                });
        }

        function createWindowCard(item, index) {
            const card = document.createElement('div');
            card.className = 'window-card fade-in-up';
            card.style.animationDelay = `${index * 0.1}s`;
            
            const statusClass = item.status === 'called' ? 'status-active' : 
                              item.status === 'waiting' ? 'status-waiting' : 'status-inactive';
            
            card.innerHTML = `
                <div class="window-number">
                    شباك ${item.window_number}
                    <span class="status-indicator ${statusClass}"></span>
                </div>
                <div class="queue-number ${item.status === 'called' ? 'pulse' : ''}">
                    ${item.number}
                </div>
                <div style="font-size: 1.2rem; color: #6c757d; margin-top: 10px;">
                    ${getStatusText(item.status)}
                </div>
            `;
            
            return card;
        }

        function getStatusText(status) {
            const statusTexts = {
                'waiting': 'في الانتظار',
                'called': 'قيد الخدمة',
                'completed': 'مكتمل',
                'announced': 'تم الإعلان'
            };
            return statusTexts[status] || status;
        }

        function pushToScreens() {
            fetch('php/push_to_screens.php')
                .then(r => r.json())
                .then(data => {
                    console.log('Push to screens results:', data.results);
                })
                .catch(err => console.error('Error pushing to screens:', err));
        }

        let systemSettings = {};
        
        // جلب إعدادات النظام
        function loadSettings() {
            fetch('php/get_settings.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        systemSettings = data.settings;
                        updateTickerMessages();
                        updateCenterName();
                    }
                })
                .catch(error => console.error('Error loading settings:', error));
        }
        
        // تحديث رسائل الشريط المتحرك
        function updateTickerMessages() {
            const tickerContent = document.querySelector('.ticker-content');
            if (tickerContent && systemSettings.ticker_messages) {
                const messages = systemSettings.ticker_messages.split('\n').filter(msg => msg.trim());
                tickerContent.innerHTML = messages.map(msg => `<span>${msg.trim()}</span>`).join('');
            }
        }
        
        // تحديث اسم المركز
        function updateCenterName() {
            const centerTitle = document.querySelector('.header-section h1');
            if (centerTitle && systemSettings.center_name) {
                centerTitle.textContent = systemSettings.center_name;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const startButton = document.getElementById('startButton');
            const welcomeScreen = document.getElementById('welcomeScreen');

            function startApp() {
                isAppStarted = true;
                welcomeScreen.style.display = 'none';
                
                // تحميل الإعدادات
                loadSettings();
                
                // بدء التحديثات
                fetchPendingCalls();
                fetchAndDisplayWindows();
                pushToScreens();
                
                // إعداد التحديثات الدورية
                setInterval(fetchPendingCalls, 5000); // كل 5 ثوان
                setInterval(fetchAndDisplayWindows, 10000); // كل 10 ثوان
                setInterval(pushToScreens, 30000); // كل 30 ثانية
                setInterval(loadSettings, 60000); // تحديث الإعدادات كل دقيقة
            }

            startButton.addEventListener('click', startApp);
        });
    </script>
</body>
</html>
