<?php
session_start();

// محاكاة تسجيل الدخول
$_SESSION['user_id'] = 6;
$_SESSION['role'] = 'counter';

echo "إنشاء دور للاختبار...\n\n";

try {
    include 'php/db.php';
    
    // حذف الأدوار القديمة
    $stmt = $conn->prepare("DELETE FROM queue WHERE user_id = ? AND date = CURDATE()");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    
    // إنشاء دور جديد
    $stmt = $conn->prepare("INSERT INTO queue (user_id, clinic, number, status, date) VALUES (?, ?, ?, ?, CURDATE())");
    
    // ✅ جميع القيم في متغيرات
    $user_id = $_SESSION['user_id'];
    $clinic  = 'قيد';
    $number  = 1;
    $status  = 'waiting';
    
    $stmt->bind_param('isis', $user_id, $clinic, $number, $status);
    $stmt->execute();
    $queueId = $conn->insert_id;
    $stmt->close();
    
    echo "✅ تم إنشاء دور جديد:\n";
    echo "- رقم الدور: $number\n";
    echo "- الخدمة: $clinic\n";
    echo "- الحالة: $status\n";
    echo "- ID: $queueId\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الدور: " . $e->getMessage();
}
?>
