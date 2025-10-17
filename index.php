<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta charset="UTF-8">
  <title>الخدمات الفعالة</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: "Cairo", Arial, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
      color: white;
      text-align: center;
      padding: 30px 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    header img {
      max-width: 80px;
      margin-bottom: 15px;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }
    header h1 {
      font-size: 28px;
      margin: 0;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    header h4 {
      font-size: 18px;
      font-weight: 400;
      margin-top: 8px;
      opacity: 0.9;
    }
    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 15px;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 25px;
      width: 100%;
      max-width: 1000px;
    }
    .service-card {
      background: white;
      border: 3px solid #1f3c88;
      border-radius: 15px;
      padding: 35px 25px;
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      color: #1f3c88;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
    }
    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    .service-card:hover::before {
      left: 100%;
    }
    .service-card:hover {
      background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
      color: white;
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 8px 25px rgba(31, 60, 136, 0.3);
      border-color: #fff;
    }

    footer {
      position: relative;
      background: #fff;
      border-top: 1px solid #ddd;
      text-align: center;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 15px;
      padding: 15px;
    }
    .footer-links a {
      font-size: 16px;
      padding: 12px 20px;
      border-radius: 8px;
      min-width: 160px;
      text-decoration: none;
      color: #1f3c88;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .footer-links a:hover {
      background: #1f3c88;
      color: white;
      transform: translateY(-2px);
    }

    .ticker {
        width: 100%;
        background: linear-gradient(135deg, #1f3c88 0%, #2c5aa0 100%);
        color: #fff;
        overflow: hidden;
        position: relative;
        height: 45px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

    /* رسائل التنبيه المحسنة */
    .alert-box {
      position: fixed;
      top: 20px;
      right: 50%;
      transform: translateX(50%);
      min-width: 300px;
      padding: 20px 25px;
      border-radius: 12px;
      text-align: center;
      z-index: 1000;
      display: none;
      font-size: 16px;
      font-weight: 500;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
      from { transform: translateX(50%) translateY(-20px); opacity: 0; }
      to { transform: translateX(50%) translateY(0); opacity: 1; }
    }
    
    .alert-success { 
      background: linear-gradient(135deg, #28a745 0%, #20bf6b 100%); 
      color: #fff; 
    }
    .alert-warning { 
      background: linear-gradient(135deg, #ffc107 0%, #f39c12 100%); 
      color: #000; 
    }
    .alert-error   { 
      background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%); 
      color: #fff; 
    }
    
    /* تحسينات الاستجابة */
    @media (max-width: 768px) {
      .card-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
      }
      .service-card {
        padding: 25px 15px;
        font-size: 18px;
      }
      header h1 {
        font-size: 24px;
      }
      header h4 {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>
  <!-- الهيدر المحسن -->
  <header>
    <img src="images/logo/logo.png" alt="Logo">
    <h1>مركز خدمة المواطن - النافذة الواحدة</h1>
    <h4>المس الخدمة لقطع الدور</h4>
  </header>

  <!-- المحتوى الرئيسي -->
  <main>
    <div id="clinicGrid" class="card-grid"></div>
  </main>

  <!-- الفوتر المحسن -->
  <footer>
    <div class="ticker">
    <div class="ticker-content">
        <span>✅ مركز خدمة المواطن في دمر</span>
        <span>⚠️ يرجى الاحتفاظ بتذكرة الدور</span>
        <span>📢 لا تخرج من الصالة لتتمكن من سماع النداء</span>
        <span>💡 نتمنى لكم يوماً طيباً</span>
    </div>
    </div>
  </footer>

  <!-- صندوق التنبيه المحسن -->
  <div id="alertBox" class="alert-box"></div>

  <script>
    // تحديث الإحصائيات
    function updateStats() {
        fetch('php/get_active_clinics.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Services loaded:', data.clinics.length);
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
    }
    
    // تحديث الإحصائيات كل 30 ثانية
    setInterval(updateStats, 30000);
    updateStats();

    async function generateQueueTicketImage(clinic, queueNumber) {
        const widthMm = 80;
        const pxPerMm = 7;
        const width = widthMm * pxPerMm;
        const padding = 20;

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // محتوى التذكرة
        const title = 'مركز خدمة المواطن';
        const subtitle = 'النافذة الواحدة';
        const queueLabel = 'رقم الدور:';
        const clinicLabel = 'الخدمة:';
        const footerLine1 = 'CMS System v2.0';
        const footerLine2 = 'POS Market';
        const footerMessage = 'مرحبًا بكم في مركز خدمة المواطن، نتمنى لكم يومًا سعيدًا';

        // أبعاد
        const lineSpacing = 30;
        const largeTextSize = 100;
        const normalTextSize = 25;
        const smallTextSize = 16;
        const headerH = 180;
        const queueNumberH = 130;
        const detailsH = 80;
        const footerH = 140;
        const extraSpace = 120;

        canvas.width = width;
        canvas.height = headerH + queueNumberH + detailsH + footerH + extraSpace;

        // خلفية
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, width, canvas.height);

        let y = 20;

        // تحميل صورة النسر السوري
        const eagleImage = await loadImage('images/eagle.png'); // أو ضع مسار الصورة المحلي داخل مشروعك

        const eagleWidth = 100;
        const eagleHeight = 100;
        const eagleX = (width - eagleWidth) / 2;
        ctx.drawImage(eagleImage, eagleX, y, eagleWidth, eagleHeight);
        y += eagleHeight + 35;

        // العنوان
        ctx.fillStyle = '#000';
        ctx.textAlign = 'center';
        ctx.font = `bold ${normalTextSize}px Arial`;
        ctx.fillText(title, width / 2, y);
        y += lineSpacing;

        ctx.font = `${normalTextSize - 4}px Arial`;
        ctx.fillText(subtitle, width / 2, y);
        y += lineSpacing + 10;

        // خط فاصل
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.strokeStyle = '#999';
        ctx.lineWidth = 1;
        ctx.stroke();
        y += 40;

        // رقم الدور
        ctx.font = `bold ${largeTextSize}px Arial`;
        ctx.fillStyle = '#000';
        ctx.textAlign = 'center';
        ctx.fillText(String(queueNumber), width / 2, y + largeTextSize * 0.8);
        ctx.font = `bold ${normalTextSize}px Arial`;
        ctx.fillText(queueLabel, width / 2, y - 2);
        y += queueNumberH;

        // خط فاصل
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
        y += 40;

        // اسم الخدمة
        ctx.font = `bold ${normalTextSize}px Arial`;
        ctx.textAlign = 'right';
        ctx.fillText(`${clinicLabel} ${clinic}`, width - padding, y);
        y += lineSpacing + 10;

        // التاريخ والوقت
        const now = new Date();
        const dateString = now.toLocaleDateString('ar-EG', { year: 'numeric', month: '2-digit', day: '2-digit' });
        const timeString = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', hour12: true });
        ctx.font = `bold ${smallTextSize}px Arial`;
        ctx.textAlign = 'center';
        ctx.fillText(dateString, width / 2, y);
        y += lineSpacing - 10;
        ctx.fillText(timeString, width / 2, y);
        y += lineSpacing + 10;

        // خط فاصل صغير قبل التذييل
        ctx.beginPath();
        ctx.moveTo(padding + 10, y);
        ctx.lineTo(width - padding - 10, y);
        ctx.strokeStyle = '#ccc';
        ctx.lineWidth = 0.5;
        ctx.stroke();
        y += 30;

        // التذييل
        ctx.font = `${smallTextSize}px Arial`;
        ctx.fillStyle = '#333';
        ctx.fillText(footerLine1, width / 2, y);
        y += lineSpacing - 10;
        ctx.fillText(footerLine2, width / 2, y);
        y += lineSpacing;

        // رسالة ترحيبية أنيقة
        ctx.font = `italic ${smallTextSize + 2}px Arial`;
        ctx.fillStyle = '#444';
        ctx.fillText(footerMessage, width / 2, y);

        return canvas.toDataURL('image/png');
    }

    // دالة لتحميل الصورة بشكل آمن قبل الرسم
    function loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    }


        document.addEventListener("DOMContentLoaded", function() {
            // المسار الافتراضي لملف جلب العيادات
            fetch('php/get_active_clinics.php') 
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const grid = document.getElementById('clinicGrid');
                        data.clinics.forEach(clinic => {
                            let card = document.createElement('div');
                            card.className = 'card';
                            card.innerHTML = `<div class="service-card"><h5 class="card-title">${clinic.clinic}</h5></div>`;
                            card.addEventListener('click', function() {
                                console.log(`Attempting to add queue for: ${clinic.clinic}`);
                                addQueue(clinic.user_ids, clinic.w_number, clinic.clinic);
                            });
                            grid.appendChild(card);
                        });
                    } else {
                        showAlert('فشل في جلب العيادات', 'error');
                    }
                })
                .catch(error => console.error('Error fetching clinics:', error));
        });

        function addQueue(userIds, w_number, clinicName) { 
            // 1. الخطوة الأولى: تسجيل الدور في قاعدة البيانات
            fetch('php/add_queue.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_ids: userIds, w_number: w_number, clinic: clinicName }) 
            })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('الرد من الخادم ليس JSON صالح:\n' + text);
                }
                
                if (data.status !== 'success' || !data.number || !data.clinic) {
                     // رمي خطأ حتى لا ننتقل لخطوة الطباعة
                     throw new Error(`فشل في إضافة الدور أو البيانات غير مكتملة: ${data.message||'غير معروف'}`);
                }
                
                // 2. الخطوة الثانية: توليد الصورة
                return generateQueueTicketImage(data.clinic, data.number)
                    .then(imageDataURL => {
                        console.log('Image generated successfully, sending to printer...');
                        
                        // 3. الخطوة الثالثة: إرسال الصورة المولدة إلى سكربت الطباعة
                        return fetch('php/print_image.php', { 
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ image: imageDataURL })
                        })
                        .then(printResponse => printResponse.json())
                        .then(printData => {
                            // دمج نتائج الإضافة والطباعة
                            return { 
                                status: printData.status, // success أو partial_success
                                number: data.number,
                                print_message: printData.message
                            };
                        });
                    });
            })
            .then(result => {
                // عرض النتيجة النهائية للمستخدم
                if (result.status === 'success') {
                    showAlert(`✅ تم إضافة الدور وطباعة التذكرة<br>رقم الدور: <b>${data.number}</b>`, 'success');
                } else if (result.status === 'partial_success') {
                    showAlert(`⚠️ تمت إضافة الدور لكن فشلت الطباعة<br>${data.print_error}`, 'warning');
                } else {
                     // هذه حالة غير متوقعة لأنها يجب أن تعالج في Catch
                     showAlert('❌ حدث خطأ غير متوقع', 'error');
                }
            })
            .then(printResponse => printResponse.text()) // تغيير إلى .text() لجلب النص الخام
            .then(responseText => {
                // حاول تحليل النص الخام يدوياً
                let printData;
                try {
                    printData = JSON.parse(responseText);
                } catch (e) {
                    // إذا فشل التحليل، اعرض النص الخام لمساعدتك في Debugging
                    console.error("Failed to parse JSON response from printer script. Raw response:", responseText);
                    throw new Error(`فشل تحليل الرد من الطابعة: ${e.message}. الرد الخام: ${responseText.substring(0, 100)}...`);
                }

                // إذا تم التحليل بنجاح، قم بإرجاع البيانات
                return { 
                    status: printData.status, 
                    number: data.number,
                    print_message: printData.message
                };
            });
        }

    function showAlert(message, type) {
      const box = document.getElementById("alertBox");
      box.className = `alert-box alert-${type}`;
      box.innerHTML = message;
      box.style.display = "block";
      setTimeout(() => { box.style.display = "none"; }, 4000);
    }
  </script>
</body>
</html>
