<?php
session_start();
header('Content-Type: application/json');

try {
    include 'db.php';
    
    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'غير مصرح بالوصول']);
        exit();
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $queueId = $input['queue_id'] ?? 0;
    $targetUserId = $input['target_user_id'] ?? 0;
    $currentUserId = $_SESSION['user_id'];
    
    if ($queueId <= 0 || $targetUserId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير صحيحة']);
        exit();
    }
    
    // التحقق من أن الدور موجود ومملوك للمستخدم الحالي
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number as current_window 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.id = ? AND q.user_id = ? AND q.date = CURDATE()
    ");
    $stmt->bind_param('ii', $queueId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'الدور غير موجود أو غير مملوك لك']);
        exit();
    }
    
    $queueData = $result->fetch_assoc();
    $stmt->close();
    
    // التحقق من أن المستخدم الهدف موجود وله نفس الخدمة
    $stmt = $conn->prepare("
        SELECT u.id, u.window_number, uc.clinic 
        FROM queue_users u 
        JOIN user_clinics uc ON u.id = uc.user_id 
        WHERE u.id = ? AND uc.clinic = ? AND u.role = 'counter'
    ");
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
    $conn->begin_transaction();
    
    try {
        // 1. حذف الدور من الشباك الحالي
        $stmt = $conn->prepare("DELETE FROM queue WHERE id = ?");
        $stmt->bind_param('i', $queueId);
        $stmt->execute();
        $stmt->close();
        
        // 2. إنشاء دور جديد في الشباك الهدف
        $stmt = $conn->prepare("
            INSERT INTO queue (user_id, clinic, number, status, date, created_at, transferred_from, transferred_to, transferred_at) 
            VALUES (?, ?, ?, 'waiting', CURDATE(), NOW(), ?, ?, NOW())
        ");
        $stmt->bind_param('isiiii', $targetUserId, $queueData['clinic'], $queueData['number'], $currentUserId, $targetUserId);
        $stmt->execute();
        $newQueueId = $conn->insert_id;
        $stmt->close();
        
        // 3. تسجيل عملية التحويل
        $stmt = $conn->prepare("
            INSERT INTO queue_transfers (original_queue_id, new_queue_id, from_user_id, to_user_id, clinic, number, transferred_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('iiiisi', $queueId, $newQueueId, $currentUserId, $targetUserId, $queueData['clinic'], $queueData['number']);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'تم تحويل الدور بنجاح',
            'new_queue_id' => $newQueueId,
            'target_window' => $targetUser['window_number']
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Transfer queue error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في التحويل: ' . $e->getMessage()]);
}
?>