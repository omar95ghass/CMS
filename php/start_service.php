<?php
session_start();
header('Content-Type: application/json');

// إيقاف عرض الأخطاء في الإخراج
error_reporting(0);
ini_set('display_errors', 0);

try {
    include 'db.php';
    
    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'غير مصرح بالوصول']);
        exit();
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $number = $input['number'] ?? 0;
    $clinic = $input['clinic'] ?? '';
    $currentUserId = $_SESSION['user_id'];
    
    if ($number <= 0 || empty($clinic)) {
        echo json_encode(['status' => 'error', 'message' => 'رقم الدور والخدمة مطلوبان']);
        exit();
    }
    
    // البحث عن الدور المحدد
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.number = ? AND q.clinic = ? AND q.date = CURDATE() 
        AND q.status IN ('called', 'announced')
    ");
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
    }
    
    $stmt->bind_param('iis', $currentUserId, $number, $clinic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'الدور غير موجود أو لم يتم نداؤه بعد']);
        exit();
    }
    
    $queueData = $result->fetch_assoc();
    $stmt->close();
    
    // تحديث حالة الدور إلى 'serving' لبدء الخدمة
    $stmt = $conn->prepare("UPDATE queue SET status = 'serving' WHERE id = ?");
    if (!$stmt) {
        throw new Exception("خطأ في إعداد استعلام التحديث: " . $conn->error);
    }
    
    $stmt->bind_param('i', $queueData['id']);
    if (!$stmt->execute()) {
        throw new Exception("فشل في تحديث حالة الدور: " . $stmt->error);
    }
    $stmt->close();
    
    // تسجيل بدء الخدمة
    error_log("Service started: Number $number (ID: {$queueData['id']}) by user $currentUserId");
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'تم بدء الخدمة بنجاح',
        'number' => $number,
        'clinic' => $clinic,
        'window_number' => $queueData['window_number']
    ]);
    
} catch (Exception $e) {
    error_log("Start service error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في بدء الخدمة: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Start service fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في بدء الخدمة: ' . $e->getMessage()]);
}
?>