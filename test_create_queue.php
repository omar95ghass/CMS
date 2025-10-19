<?php
session_start();

// محاكاة تسجيل الدخول
$_SESSION['user_id'] = 1;
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
    $status = 'waiting';
    $stmt->bind_param('isi', $_SESSION['user_id'], 'قيد', 1, $status);
    $stmt->execute();
    $queueId = $conn->insert_id;
    $stmt->close();
    
    echo "✅ تم إنشاء دور جديد:\n";
    echo "- رقم الدور: 1\n";
    echo "- الخدمة: قيد\n";
    echo "- الحالة: waiting\n";
    echo "- ID: $queueId\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الدور: " . $e->getMessage();
}
?>