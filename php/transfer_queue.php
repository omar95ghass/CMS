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
    $targetUserId = $input['target_user_id'] ?? 0;
    $currentUserId = $_SESSION['user_id'];
    
    if ($targetUserId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير صحيحة']);
        exit();
    }
    
    // البحث عن الدور بالرقم والخدمة بدلاً من ID
    $number = $input['number'] ?? 0;
    $clinic = $input['clinic'] ?? '';
    
    if ($number <= 0 || empty($clinic)) {
        echo json_encode(['status' => 'error', 'message' => 'رقم الدور والخدمة مطلوبان']);
        exit();
    }
    
    // التحقق من أن الدور موجود ومملوك للمستخدم الحالي
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number as current_window 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.number = ? AND q.clinic = ? AND q.date = CURDATE() AND q.status = 'waiting'
    ");
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
    }
    
    $stmt->bind_param('iis', $currentUserId, $number, $clinic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'الدور غير موجود أو غير متاح للتحويل']);
        exit();
    }
    
    $queueData = $result->fetch_assoc();
    $queueId = $queueData['id']; // الحصول على ID من النتيجة
    $stmt->close();
    
    // التحقق من أن المستخدم الهدف موجود وله نفس الخدمة
    $stmt = $conn->prepare("
        SELECT u.id, u.window_number, uc.clinic 
        FROM queue_users u 
        JOIN user_clinics uc ON u.id = uc.user_id 
        WHERE u.id = ? AND uc.clinic = ? AND u.role = 'counter'
    ");
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد استعلام المستخدم الهدف: " . $conn->error);
    }
    
    $stmt->bind_param('is', $targetUserId, $queueData['clinic']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'المستخدم الهدف غير موجود أو لا يخدم هذه الخدمة']);
        exit();
    }
    
    $targetUser = $result->fetch_assoc();
    $stmt->close();
    
    // بدء المعاملة
    if (!$conn->begin_transaction()) {
        throw new Exception("فشل في بدء المعاملة: " . $conn->error);
    }
    
    try {
        // 1. حذف الدور من الشباك الحالي
        $stmt = $conn->prepare("DELETE FROM queue WHERE id = ?");
        if (!$stmt) {
            throw new Exception("خطأ في إعداد استعلام الحذف: " . $conn->error);
        }
        $stmt->bind_param('i', $queueId);
        if (!$stmt->execute()) {
            throw new Exception("فشل في حذف الدور: " . $stmt->error);
        }
        $stmt->close();
        
        // 2. إنشاء دور جديد في الشباك الهدف
        $stmt = $conn->prepare("
            INSERT INTO queue (user_id, clinic, number, status, date, created_at, transferred_from, transferred_to, transferred_at) 
            VALUES (?, ?, ?, 'waiting', CURDATE(), NOW(), ?, ?, NOW())
        ");
        if (!$stmt) {
            throw new Exception("خطأ في إعداد استعلام الإنشاء: " . $conn->error);
        }
        $stmt->bind_param('isiii', $targetUserId, $queueData['clinic'], $queueData['number'], $currentUserId, $targetUserId);
        if (!$stmt->execute()) {
            throw new Exception("فشل في إنشاء الدور الجديد: " . $stmt->error);
        }
        $newQueueId = $conn->insert_id;
        $stmt->close();
        
        // 3. تسجيل عملية التحويل
        $stmt = $conn->prepare("
            INSERT INTO queue_transfers (original_queue_id, new_queue_id, from_user_id, to_user_id, clinic, number, transferred_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            throw new Exception("خطأ في إعداد استعلام تسجيل التحويل: " . $conn->error);
        }
        $stmt->bind_param('iiiis', $queueData['id'], $newQueueId, $currentUserId, $targetUserId, $queueData['clinic'], $queueData['number']);
        if (!$stmt->execute()) {
            throw new Exception("فشل في تسجيل عملية التحويل: " . $stmt->error);
        }
        $stmt->close();
        
        if (!$conn->commit()) {
            throw new Exception("فشل في تأكيد المعاملة: " . $conn->error);
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'تم تحويل الدور بنجاح',
            'new_queue_id' => $newQueueId,
            'target_window' => $targetUser['window_number']
        ]);
        
    } catch (Exception $e) {
        if (!$conn->rollback()) {
            error_log("فشل في إلغاء المعاملة: " . $conn->error);
        }
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Transfer queue error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في التحويل: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Transfer queue fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في التحويل: ' . $e->getMessage()]);
}
?>