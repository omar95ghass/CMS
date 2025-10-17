<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض الرقم</title>
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            height: 95%;
            min-width: 700px;
            text-align: center;
        }
        .container {
            min-width: 100%;
        }
        table {
            font-size: 30px;
            font-weight: bold;
        }
        td {
            font-size: 50px;
        }

        footer {
      position: relative;
      background: #fff;
      border-top: 1px solid #ddd;
      text-align: center;
    }
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 15px;
      padding: 10px;
    }
    .footer-links a {
      font-size: 16px;
      padding: 12px 20px;
      border-radius: 8px;
      min-width: 160px;
    }

    .ticker {
        width: 100%;
        background: #1f3c88;
        color: #fff;
        overflow: hidden;
        position: relative;
        height: 40px;
        display: flex;
        align-items: center;
        }

        .ticker-content {
        display: inline-block;
        white-space: nowrap;
        animation: ticker 25s linear infinite;
        }

        .ticker-content span {
        display: inline-block;
        margin-left: 80px; /* فراغ بين الرسائل */
        font-size: 16px;
        }

        /* الحركة المستمرة */
        @keyframes ticker {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
        }

        .logo { width: 150px; margin-bottom: 20px; }
        #displayNumber { font-size: 8rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container w-100">
        <div id="welcomeScreen" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #fff; z-index: 999; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h2>أهلاً بك</h2>
            <p>الرجاء النقر على الزر لتفعيل الصوت</p>
            <button id="startButton" class="btn btn-primary btn-lg">ابدأ</button>
        </div>
        <div>
            <h1>مركز خدمة المواطن - النافذة الواحدة</h1>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered text-center" id="windowsTable">
            <thead class="table-dark">
                <tr id="headerRow"></tr>
            </thead>
            <tbody>
                <tr id="dataRow"></tr>
            </tbody>
            </table>
        </div>
    </div>
    <footer>
        <div class="ticker">
        <div class="ticker-content">
            <span>✅ مركز خدمة المواطن في دمر</span>
            <span>⚠️ يرجى الاحتفاظ بتذكرة الدور</span>
            <span>📢 لا تخرج من الصالة لتتمكن من سماع النداء</span>
            <span> نتمنى لكم يوماً طيباً</span>
        </div>
        </div>

    </footer>
    <script>
  let playbackQueue = [];
  let isPlaying     = false;
  let audioWorker;

    function playNumber(number, windowNumber, onComplete) {
        let q = [];
        // الدالة e الآن تأخذ اسم الملف فقط (بدون امتداد .mp3)
        function e(filename){ q.push(filename); }

        e('number'); // كانت e('number.mp3')
        
        if (number>=1 && number<=19) {
            e(number); // كانت e(number+'.mp3')
        } else {
            let h=Math.floor(number/100)*100,
                t=Math.floor((number%100)/10)*10,
                o=number%10;
            if(h){ e(h); if(t||o) e('and'); } // كانت e(h+'.mp3'), e('and.mp3')
            if(o){ e(o); if(t) e('and'); } // كانت e(o+'.mp3'), e('and.mp3')
            if(t) e(t); // كانت e(t+'.mp3')
        }
        e('goto'); // كانت e('goto.mp3')
        e(windowNumber); // كانت e(windowNumber+'.mp3')

        function next(){
            if(q.length===0) return onComplete && onComplete();
            let a = new Audio();
            let filename = q.shift();
            
            // هنا يتم إضافة صيغتين مختلفتين
            a.src = 'audio/ar/' + filename + '.mp3';
            // a.src = 'audio/ar/' + filename + '.ogg';

            a.onended = next;
            // يجب أن تضع this.play() لتعمل
            a.play().catch(error => {
                console.error('Error playing audio:', error);
                // إذا فشل التشغيل، انتقل إلى الصوت التالي
                next();
            });
        }
        next();
    }

  // علامة Announce بعد النطق
  function markAnnounced(id) {
    fetch('php/mark_announced.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'id=' + encodeURIComponent(id)
    }).catch(console.error);
  }

  // جلب النداءات دون تغيير الحالة
  function fetchPendingCalls() {
    fetch('php/get_pending_calls.php')
      .then(r=>r.json())
      .then(data=> {
        if (data.status==='success') {
          data.calls.forEach(c => playbackQueue.push(c));
          playFromQueue();
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
      // بعد النطق علّم السجل
      markAnnounced(item.id);
      // ثم نطق التالي بعد 2 ثواني
      setTimeout(playFromQueue, 2000);
    });
  }

  function fetchAndDisplayTable() {
        fetch('php/get_current_number.php')
        .then(r => r.json())
        .then(data => {
            const headerRow = document.getElementById('headerRow');
            const dataRow = document.getElementById('dataRow');
            headerRow.innerHTML = '';
            dataRow.innerHTML = '';

            if (data.status === 'success' && data.calls.length > 0) {
            // ترتيب حسب رقم الشباك
            data.calls.sort((a, b) => a.window_number - b.window_number);

            data.calls.forEach(item => {
                const th = document.createElement('th');
                th.textContent = `شباك ${item.window_number}`;
                headerRow.appendChild(th);

                const td = document.createElement('td');
                td.textContent = item.number;
                dataRow.appendChild(td);
            });
            } else {
            const th = document.createElement('th');
            th.colSpan = 10;
            th.textContent = 'لا توجد بيانات حالياً';
            headerRow.appendChild(th);

            const td = document.createElement('td');
            td.colSpan = 10;
            td.textContent = '-';
            dataRow.appendChild(td);
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('headerRow').innerHTML = '<th colspan="10">خطأ في الاتصال بالخادم</th>';
            document.getElementById('dataRow').innerHTML = '<td colspan="10">-</td>';
        });
    }

    function pushToScreens() {
        fetch('php/push_to_screens.php')
        .then(r=>r.json())
        .then(data=>{
            console.log('Push to screens results:', data.results);
        })
        .catch(err=>console.error('Error pushing to screens:', err));
    }   

  document.addEventListener('DOMContentLoaded', () => {

    const startButton = document.getElementById('startButton');
    const welcomeScreen = document.getElementById('welcomeScreen');

    function startApp() {
        // أخفي شاشة الترحيب
        welcomeScreen.style.display = 'none';
    }

    fetchPendingCalls();               // أول دفعة
    setInterval(fetchPendingCalls, 3000); // جلب دوري
    fetchAndDisplayTable();
      setInterval(fetchAndDisplayTable, 10000);
      pushToScreens();
      //setInterval(pushToScreens, 10000);

        startButton.addEventListener('click', startApp);
  });
</script>
</body>
</html>
