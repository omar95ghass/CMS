<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض الرقم</title>
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            /* flex-direction: column; */
            justify-content: center;
            align-items: center;
            height: 100vh;
            min-width: 700px;
            text-align: center;
        }
        .container {
            min-width: 90%;
        }
        table {
            font-size: 30px;
            font-weight: bold;
        }
        td {
            font-size: 50px;
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
        <img class="logo" src="images/logo/logo.png" alt="Logo">
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

    <script>
  let playbackQueue = [];
  let isPlaying     = false;
  let audioContext; // متغير لـ Web Audio API

  // دالة لتحميل وتشغيل ملف صوتي باستخدام Web Audio API
  function playSound(filename, onComplete) {
    if (!audioContext) {
      console.error("AudioContext is not initialized.");
      return onComplete();
    }

    const path = `audio/ar/${filename}`;
    const mp3_path = `${path}.mp3`;
    const ogg_path = `${path}.ogg`;

    // جلب الملف الصوتي
    fetch(mp3_path)
      .then(response => {
        if (response.ok) return response.arrayBuffer();
        // إذا فشل تحميل MP3، جرب OGG
        return fetch(ogg_path).then(res => {
          if (res.ok) return res.arrayBuffer();
          throw new Error('Failed to load both MP3 and OGG files');
        });
      })
      .then(buffer => audioContext.decodeAudioData(buffer))
      .then(audioBuffer => {
        const source = audioContext.createBufferSource();
        source.buffer = audioBuffer;
        source.connect(audioContext.destination);
        source.onended = onComplete;
        source.start(0);
      })
      .catch(error => {
        console.error(`Error playing audio for '${filename}':`, error);
        onComplete(); // انتقل إلى الصوت التالي حتى لو فشل
      });
  }

  // دالة النطق المُعدّلة
  function playNumber(number, windowNumber, onComplete) {
    let q = [];
    function e(filename){ q.push(filename); }

    e('number');
    if (number>=1 && number<=19) {
      e(number);
    } else {
      let h=Math.floor(number/100)*100,
          t=Math.floor((number%100)/10)*10,
          o=number%10;
      if(h){ e(h); if(t||o) e('and'); }
      if(o){ e(o); if(t) e('and'); }
      if(t) e(t);
    }
    e('goto');
    e(windowNumber);

    function next(){
      if(q.length===0) return onComplete && onComplete();
      let filename = q.shift();
      playSound(filename, next);
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
      markAnnounced(item.id);
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

  // دالة بدء التطبيق
  function startApp() {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }

    const welcomeScreen = document.getElementById('welcomeScreen');
    welcomeScreen.style.display = 'none';

    // ابدأ جلب البيانات وتشغيل الصوت هنا
    fetchPendingCalls();
    setInterval(fetchPendingCalls, 3000);
    fetchAndDisplayTable();
    setInterval(fetchAndDisplayTable, 10000);
    pushToScreens();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const startButton = document.getElementById('startButton');
    startButton.addEventListener('click', startApp);

    // يجب أن يتم استدعاء هذه الدوال فقط بعد النقر على زر "ابدأ"
    // fetchPendingCalls();
    // setInterval(fetchPendingCalls, 3000);
    // fetchAndDisplayTable();
    // setInterval(fetchAndDisplayTable, 10000);
    // pushToScreens();
  });
</script>
</body>
</html>