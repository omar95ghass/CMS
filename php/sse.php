 
<?php
session_start();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// الاتصال بقاعدة البيانات
include 'db.php';

// اجعل الخادم يتحسس التغييرات كل 5 ثوانٍ
while (true) {
    // جلب الرقم المطلوب الحالي من قاعدة البيانات
    $result = $conn->query("SELECT number FROM queue WHERE status='called' ORDER BY id DESC LIMIT 1");
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $number = $row['number'];
        
        // إرسال الرقم إلى العميل
        echo "data: {$number}\n\n";
        flush();
    } else {
        // في حال عدم وجود رقم تم النداء عليه
        echo "data: No number called\n\n";
        flush();
    }

    // انتظر 5 ثوانٍ قبل التحقق مرة أخرى
    sleep(5);
}
$conn->close();
?>
