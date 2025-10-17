<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta charset="UTF-8">
  <title>الخدمات الفعالة</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="images/logo/logo.ico">
  <style>
    body {
      font-family: "Cairo", Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: #1f3c88;
      color: white;
      text-align: center;
      padding: 20px 10px;
    }
    header img {
      max-width: 80px;
      margin-bottom: 10px;
    }
    header h1 {
      font-size: 26px;
      margin: 0;
    }
    header h4 {
      font-size: 16px;
      font-weight: 300;
      margin-top: 5px;
    }
    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 15px;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      width: 100%;
      max-width: 1000px;
    }
    .service-card {
      background: white;
      border: 2px solid #1f3c88;
      border-radius: 12px;
      padding: 30px 20px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      color: #1f3c88;
      cursor: pointer;
      transition: all 0.2s;
    }
    .service-card:hover {
      background: #e9efff;
      transform: scale(1.03);
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
  </style>
</head>
<body>
  <!-- الهيدر -->
  <header>
    <img src="images/logo/logo.png" alt="Logo">
    <h1>مركز خدمة المواطن - النافذة الواحدة</h1>
    <h4>المس الخدمة لقطع الدور</h4>
  </header>

  <!-- المحتوى الرئيسي -->
  <main>
    <div id="clinicGrid" class="card-grid"></div>
  </main>

  <!-- الفوتر -->
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

  <!-- صندوق التنبيه -->
  <div id="alertBox" class="alert-box"></div>

  <script>
    
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
