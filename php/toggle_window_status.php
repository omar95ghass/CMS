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
    $windowId = $input['window_id'] ?? 0;
    $newStatus = $input['status'] ?? '';
    
    if ($windowId <= 0 || empty($newStatus)) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير صحيحة']);
        exit();
    }
    
    // التحقق من أن المستخدم يملك هذا الشباك أو هو admin
    $stmt = $conn->prepare("
        SELECT id, window_number 
        FROM queue_users 
        WHERE id = ? AND (id = ? OR ? = 'admin')
    ");
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
    }
    
    $currentUserId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    $stmt->bind_param('iis', $windowId, $currentUserId, $userRole);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'غير مصرح بتعديل هذا الشباك']);
        exit();
    }
    
    $windowData = $result->fetch_assoc();
    $stmt->close();
    
    // تحديث حالة الشباك
    $stmt = $conn->prepare("UPDATE queue_users SET status = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("خطأ في إعداد استعلام التحديث: " . $conn->error);
    }
    
    $stmt->bind_param('si', $newStatus, $windowId);
    if (!$stmt->execute()) {
        throw new Exception("فشل في تحديث حالة الشباك: " . $stmt->error);
    }
    $stmt->close();
    
    // تسجيل العملية
    $action = $newStatus === 'closed' ? 'إغلاق' : 'فتح';
    error_log("Window status changed: Window {$windowData['window_number']} {$action} by user $currentUserId");
    
    echo json_encode([
        'status' => 'success',
        'message' => "تم {$action} الشباك بنجاح",
        'window_id' => $windowId,
        'window_number' => $windowData['window_number'],
        'new_status' => $newStatus
    ]);
    
} catch (Exception $e) {
    error_log("Toggle window status error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في تحديث حالة الشباك: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Toggle window status fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في تحديث حالة الشباك: ' . $e->getMessage()]);
}
?>