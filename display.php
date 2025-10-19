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
        
        /* شريط حالة الشبابيك */
        .windows-status-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
            z-index: 1000;
            border-top: 3px solid #3498db;
        }
        
        .status-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .status-bar-header h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .status-legend {
            display: flex;
            gap: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-indicator.serving {
            background: #f39c12;
            box-shadow: 0 0 10px rgba(243, 156, 18, 0.5);
        }
        
        .status-indicator.available {
            background: #27ae60;
            box-shadow: 0 0 10px rgba(39, 174, 96, 0.5);
        }
        
        .status-indicator.closed {
            background: #e74c3c;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }
        
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .window-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .window-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .window-card.serving {
            border-color: #f39c12;
            background: rgba(243, 156, 18, 0.1);
        }
        
        .window-card.available {
            border-color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
        }
        
        .window-card.closed {
            border-color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
        }
        
        .window-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .window-status {
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .window-clinic {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .toggle-status-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .toggle-status-btn:hover {
            background: #2980b9;
        }
        
        .toggle-status-btn.closed {
            background: #e74c3c;
        }
        
        .toggle-status-btn.closed:hover {
            background: #c0392b;
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
        let continuousAnnouncements = new Map(); // تتبع النداءات المستمرة
        let announcementIntervals = new Map(); // تتبع الفترات الزمنية للنداءات

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
                a.onerror = () => {
                    console.error('Audio error for:', filename);
                    setTimeout(next, 100);
                };
                a.play().catch(error => {
                    console.error('Error playing audio:', error);
                    // في حالة فشل تشغيل الصوت، انتقل إلى الملف التالي
                    setTimeout(next, 100);
                });
            }
            next();
        }

        // تسجيل تشغيل النداء (بدون تغيير الحالة)
        function markPlayed(id) {
            // إضافة المعرف إلى lastProcessedIds للنداءات الجديدة فقط
            lastProcessedIds.add(id);
            console.log('Marked as played:', id, 'Total processed:', lastProcessedIds.size);
        }

        // بدء النداء المستمر
        function startContinuousAnnouncement(call) {
            const callKey = `${call.id}_${call.number}_${call.clinic}`;
            
            // إذا كان النداء مستمر بالفعل، لا نبدأ نداء جديد
            if (continuousAnnouncements.has(callKey)) {
                console.log('Continuous announcement already exists for:', callKey);
                return;
            }
            
            console.log('Starting continuous announcement for:', callKey);
            
            // إضافة النداء إلى قائمة النداءات المستمرة
            continuousAnnouncements.set(callKey, {
                id: call.id,
                number: call.number,
                clinic: call.clinic,
                windowNumber: call.window_number,
                startTime: Date.now(),
                announcementCount: 0
            });
            
            // تشغيل النداء فوراً
            playAnnouncement(call);
            
            // إعداد النداء المتكرر كل 20 ثانية
            const intervalId = setInterval(() => {
                const announcement = continuousAnnouncements.get(callKey);
                if (announcement) {
                    announcement.announcementCount++;
                    console.log(`Announcement #${announcement.announcementCount} for ${callKey}`);
                    playAnnouncement(call);
                }
            }, 20000); // كل 20 ثانية
            
            announcementIntervals.set(callKey, intervalId);
        }

        // تشغيل النداء
        function playAnnouncement(call) {
            if (isPlaying) {
                // إذا كان هناك نداء قيد التشغيل، أضف إلى قائمة الانتظار
                playbackQueue.push(call);
                console.log('Added to playback queue:', call.number, 'Queue length:', playbackQueue.length);
                return;
            }
            
            console.log('Playing announcement:', call.number, call.clinic, 'Window:', call.window_number);
            isPlaying = true;
            playNumber(call.number, call.window_number, () => {
                isPlaying = false;
                console.log('Finished playing announcement:', call.number);
                // تشغيل النداء التالي في قائمة الانتظار
                if (playbackQueue.length > 0) {
                    setTimeout(() => {
                        const nextCall = playbackQueue.shift();
                        playAnnouncement(nextCall);
                    }, 1000); // تم تقليل الفترة إلى ثانية واحدة
                }
            });
        }

        // فحص النداءات المستمرة
        function checkContinuousAnnouncements(allCalls) {
            const activeCallIds = new Set(allCalls.map(call => call.id));
            const servingCallIds = new Set(allCalls.filter(call => call.status === 'serving').map(call => call.id));
            
            // إزالة النداءات التي لم تعد موجودة أو تم بدء الخدمة لها
            for (const [callKey, announcement] of continuousAnnouncements) {
                if (!activeCallIds.has(announcement.id) || servingCallIds.has(announcement.id)) {
                    console.log('Stopping continuous announcement for:', callKey, 'Reason:', servingCallIds.has(announcement.id) ? 'Service started' : 'Not found');
                    stopContinuousAnnouncement(callKey);
                }
            }
            
            // إضافة نداءات جديدة للنداء المستمر
            allCalls.forEach(call => {
                if (call.status === 'called' && !continuousAnnouncements.has(`${call.id}_${call.number}_${call.clinic}`)) {
                    console.log('Adding new call to continuous announcements:', call.number);
                    startContinuousAnnouncement(call);
                }
            });
        }

        // إيقاف النداء المستمر
        function stopContinuousAnnouncement(callKey) {
            // إزالة من قائمة النداءات المستمرة
            continuousAnnouncements.delete(callKey);
            
            // إيقاف الفترة الزمنية
            const intervalId = announcementIntervals.get(callKey);
            if (intervalId) {
                clearInterval(intervalId);
                announcementIntervals.delete(callKey);
            }
            
            console.log('Stopped continuous announcement for:', callKey);
        }

        // إيقاف النداء المستمر لرقم محدد
        function stopContinuousAnnouncementForNumber(number) {
            for (const [callKey, announcement] of continuousAnnouncements) {
                if (announcement.number === number) {
                    console.log('Stopping continuous announcement for number:', number);
                    stopContinuousAnnouncement(callKey);
                    break;
                }
            }
        }

        // دالة عامة لإيقاف النداء المستمر (يمكن استدعاؤها من counter.php)
        window.stopContinuousAnnouncementForNumber = stopContinuousAnnouncementForNumber;

        // دالة لإيقاف جميع النداءات المستمرة
        function stopAllContinuousAnnouncements() {
            console.log('Stopping all continuous announcements');
            for (const [callKey, announcement] of continuousAnnouncements) {
                stopContinuousAnnouncement(callKey);
            }
        }

        // دالة لإعادة تشغيل جميع النداءات المستمرة
        function restartAllContinuousAnnouncements() {
            console.log('Restarting all continuous announcements');
            stopAllContinuousAnnouncements();
            setTimeout(() => {
                startAllContinuousAnnouncements();
            }, 1000);
        }

        // دالة لعرض إحصائيات مفصلة
        function showDetailedStats() {
            console.log('=== Detailed Statistics ===');
            console.log('Total continuous announcements:', continuousAnnouncements.size);
            console.log('Total intervals:', announcementIntervals.size);
            console.log('Playback queue length:', playbackQueue.length);
            console.log('Is playing:', isPlaying);
            console.log('Last processed IDs count:', lastProcessedIds.size);
            console.log('App started:', isAppStarted);
            console.log('===========================');
        }

        // دالة لتنظيف النظام
        function cleanupSystem() {
            console.log('Cleaning up system...');
            stopAllContinuousAnnouncements();
            playbackQueue = [];
            lastProcessedIds.clear();
            isPlaying = false;
            console.log('System cleaned up');
        }

        // دالة لاختبار النظام
        function testSystem() {
            console.log('Testing system...');
            fetch('php/get_pending_calls.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('System test passed - got', data.calls.length, 'calls');
                        data.calls.forEach(call => {
                            console.log('Call:', call.number, call.clinic, call.status);
                        });
                    } else {
                        console.log('System test failed:', data.message);
                    }
                })
                .catch(error => {
                    console.log('System test error:', error);
                });
        }

        // دالة لعرض المساعدة
        function showHelp() {
            console.log('=== Help ===');
            console.log('Ctrl+D - Show continuous announcements status');
            console.log('Ctrl+S - Start all continuous announcements');
            console.log('Ctrl+X - Stop all continuous announcements');
            console.log('Ctrl+R - Restart all continuous announcements');
            console.log('Ctrl+I - Show detailed statistics');
            console.log('Ctrl+C - Cleanup system');
            console.log('Ctrl+T - Test system');
            console.log('Ctrl+H - Show this help');
            console.log('===========');
        }

        // دالة لعرض معلومات النظام
        function showSystemInfo() {
            console.log('=== System Info ===');
            console.log('User Agent:', navigator.userAgent);
            console.log('Screen:', screen.width + 'x' + screen.height);
            console.log('Viewport:', window.innerWidth + 'x' + window.innerHeight);
            console.log('Audio support:', typeof Audio !== 'undefined');
            console.log('Fetch support:', typeof fetch !== 'undefined');
            console.log('===================');
        }

        // دالة لعرض حالة الصوت
        function showAudioStatus() {
            console.log('=== Audio Status ===');
            console.log('Audio files path: audio/ar/');
            console.log('Number audio files: 1-19, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000');
            console.log('Special audio files: number, goto, and');
            console.log('====================');
        }

        // دالة لعرض حالة الشبابيك
        function showWindowsStatus() {
            console.log('=== Windows Status ===');
            fetch('php/get_windows_status.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Windows count:', data.windows.length);
                        data.windows.forEach(window => {
                            console.log(`Window ${window.window_number}: ${window.status} (${window.user_name})`);
                        });
                    } else {
                        console.log('Failed to get windows status:', data.message);
                    }
                })
                .catch(error => {
                    console.log('Error getting windows status:', error);
                });
        }

        // دالة لعرض حالة النظام الكاملة
        function showFullSystemStatus() {
            console.log('=== Full System Status ===');
            showContinuousAnnouncementsStatus();
            showDetailedStats();
            showSystemInfo();
            showAudioStatus();
            showWindowsStatus();
            console.log('==========================');
        }

        // دالة لبدء النداء المستمر لجميع النداءات النشطة
        function startAllContinuousAnnouncements() {
            fetch('php/get_pending_calls.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        data.calls.forEach(call => {
                            if (call.status === 'called') {
                                startContinuousAnnouncement(call);
                            }
                        });
                    }
                })
                .catch(console.error);
        }

        // دالة لعرض حالة النداءات المستمرة
        function showContinuousAnnouncementsStatus() {
            console.log('=== Continuous Announcements Status ===');
            console.log('Total active announcements:', continuousAnnouncements.size);
            for (const [callKey, announcement] of continuousAnnouncements) {
                console.log(`- ${callKey}: Count ${announcement.announcementCount}, Duration: ${Math.round((Date.now() - announcement.startTime) / 1000)}s`);
            }
            console.log('=====================================');
        }

        // إضافة دالة لعرض الحالة في الصفحة
        function addStatusDisplay() {
            const statusDiv = document.createElement('div');
            statusDiv.id = 'announcementStatus';
            statusDiv.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 10px;
                border-radius: 5px;
                font-size: 12px;
                z-index: 1000;
                max-width: 300px;
            `;
            document.body.appendChild(statusDiv);
            
            // تحديث الحالة كل 5 ثوان
            setInterval(() => {
                const status = `النداءات المستمرة: ${continuousAnnouncements.size}`;
                statusDiv.textContent = status;
            }, 5000);
        }

        // جلب النداءات الجديدة فقط
        function fetchPendingCalls() {
            if (!isAppStarted) return;
            
            fetch('php/get_pending_calls.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Fetched calls:', data.calls.length, 'calls');
                        
                        // فحص النداءات المستمرة للتأكد من أنها لا تزال تحتاج للنداء
                        checkContinuousAnnouncements(data.calls);
                        
                        // تصفية النداءات الجديدة فقط التي لم يتم معالجتها
                        const newCalls = data.calls.filter(call => {
                            // للنداءات الجديدة، تحقق من عدم معالجتها مسبقاً
                            return !lastProcessedIds.has(call.id) && call.status === 'called';
                        });
                        
                        console.log('New calls found:', newCalls.length, 'Total calls:', data.calls.length);
                        
                        newCalls.forEach(call => {
                            // بدء النداء المستمر للنداءات الجديدة
                            startContinuousAnnouncement(call);
                            lastProcessedIds.add(call.id);
                        });
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
                markPlayed(item.id);
                // نطق التالي بعد 2 ثواني
                setTimeout(playFromQueue, 2000);
            });
        }

        function fetchAnnouncedNumbers() {
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
        
        // إدارة شريط حالة الشبابيك
        let windowsStatus = {};
        
        // جلب حالة الشبابيك
        function fetchAndDisplayWindows() {
            fetch('php/get_windows_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        windowsStatus = data.windows;
                        displayWindowsStatus();
                    }
                })
                .catch(error => console.error('Error fetching windows status:', error));
        }
        
        // عرض حالة الشبابيك
        function displayWindowsStatus() {
            const windowsGrid = document.getElementById('windowsGrid');
            const statusBar = document.getElementById('windowsStatusBar');
            
            if (!windowsGrid || !statusBar) return;
            
            if (Object.keys(windowsStatus).length === 0) {
                statusBar.style.display = 'none';
                return;
            }
            
            statusBar.style.display = 'block';
            
            windowsGrid.innerHTML = Object.values(windowsStatus).map(window => {
                const statusClass = window.status || 'available';
                const statusText = getStatusText(window.status);
                
                return `
                    <div class="window-card ${statusClass}">
                        <div class="window-number">شباك ${window.window_number}</div>
                        <div class="window-status">${statusText}</div>
                        <div class="window-clinic">${window.clinic || 'غير محدد'}</div>
                        <button class="toggle-status-btn ${window.status === 'closed' ? 'closed' : ''}" 
                                onclick="toggleWindowStatus(${window.id})">
                            ${window.status === 'closed' ? 'فتح الشباك' : 'إغلاق الشباك'}
                        </button>
                    </div>
                `;
            }).join('');
        }
        
        // الحصول على نص الحالة
        function getStatusText(status) {
            switch(status) {
                case 'serving': return 'يقدم خدمة';
                case 'closed': return 'مغلق';
                default: return 'متاح';
            }
        }
        
        // تبديل حالة الشباك
        function toggleWindowStatus(windowId) {
            const newStatus = windowsStatus[windowId].status === 'closed' ? 'available' : 'closed';
            
            fetch('php/toggle_window_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    window_id: windowId,
                    status: newStatus 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    windowsStatus[windowId].status = newStatus;
                    displayWindowsStatus();
                } else {
                    alert('فشل في تحديث حالة الشباك: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error toggling window status:', error);
                alert('حدث خطأ في تحديث حالة الشباك');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const startButton = document.getElementById('startButton');
            const welcomeScreen = document.getElementById('welcomeScreen');

            function startApp() {
                isAppStarted = true;
                welcomeScreen.style.display = 'none';
                
                // تحميل الإعدادات
                loadSettings();
                
                // إضافة عرض الحالة
                addStatusDisplay();
                
                // بدء التحديثات
                fetchPendingCalls();
                fetchAnnouncedNumbers();
                fetchAndDisplayWindows();
                pushToScreens();
                
                // إعداد التحديثات الدورية
                setInterval(fetchPendingCalls, 5000); // كل 5 ثوان
                setInterval(fetchAnnouncedNumbers, 10000); // كل 10 ثوان
                setInterval(fetchAndDisplayWindows, 10000); // كل 10 ثوان
                setInterval(pushToScreens, 30000); // كل 30 ثانية
                setInterval(loadSettings, 60000); // تحديث الإعدادات كل دقيقة
                
                // إضافة اختصارات لوحة المفاتيح للتشخيص
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'd') {
                        showContinuousAnnouncementsStatus();
                    }
                    if (e.ctrlKey && e.key === 's') {
                        startAllContinuousAnnouncements();
                    }
                    if (e.ctrlKey && e.key === 'x') {
                        stopAllContinuousAnnouncements();
                    }
                    if (e.ctrlKey && e.key === 'r') {
                        restartAllContinuousAnnouncements();
                    }
                    if (e.ctrlKey && e.key === 'i') {
                        showDetailedStats();
                    }
                    if (e.ctrlKey && e.key === 'c') {
                        cleanupSystem();
                    }
                    if (e.ctrlKey && e.key === 't') {
                        testSystem();
                    }
                    if (e.ctrlKey && e.key === 'h') {
                        showHelp();
                    }
                    if (e.ctrlKey && e.key === 'n') {
                        showSystemInfo();
                    }
                    if (e.ctrlKey && e.key === 'a') {
                        showAudioStatus();
                    }
                    if (e.ctrlKey && e.key === 'w') {
                        showWindowsStatus();
                    }
                    if (e.ctrlKey && e.key === 'f') {
                        showFullSystemStatus();
                    }
                });
                
                console.log('App started! Press Ctrl+H for help');
            }

            // بدء التطبيق تلقائياً بعد 1 ثانية
            setTimeout(startApp, 1000);
            
            startButton.addEventListener('click', startApp);
        });
    </script>
    
    <!-- شريط حالة الشبابيك -->
    <div id="windowsStatusBar" class="windows-status-bar" style="display: none;">
        <div class="status-bar-header">
            <h4>حالة الشبابيك</h4>
            <div class="status-legend">
                <span class="legend-item">
                    <span class="status-indicator serving"></span>
                    يقدم خدمة
                </span>
                <span class="legend-item">
                    <span class="status-indicator available"></span>
                    متاح
                </span>
                <span class="legend-item">
                    <span class="status-indicator closed"></span>
                    مغلق
                </span>
            </div>
        </div>
        <div class="windows-grid" id="windowsGrid">
            <!-- الشبابيك ستظهر هنا -->
        </div>
    </div>
</body>
</html>
