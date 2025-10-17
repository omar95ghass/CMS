<?php
session_start();
header('Content-Type: application/json');

// إيقاف عرض الأخطاء في الإخراج
error_reporting(0);
ini_set('display_errors', 0);

try {
    include 'db.php';
    
    // جلب جميع الشبابيك مع حالتها
    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.window_number,
            u.status as window_status,
            GROUP_CONCAT(DISTINCT uc.clinic) as clinics,
            COUNT(DISTINCT q.id) as active_queues
        FROM queue_users u
        LEFT JOIN user_clinics uc ON u.id = uc.user_id
        LEFT JOIN queue q ON u.id = q.user_id AND q.date = CURDATE() AND q.status IN ('called', 'announced')
        WHERE u.role = 'counter'
        GROUP BY u.id, u.window_number, u.status
        ORDER BY u.window_number
    ");
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $windows = [];
    while ($row = $result->fetch_assoc()) {
        $status = 'available'; // افتراضي
        
        // تحديد الحالة بناءً على البيانات
        if ($row['window_status'] === 'closed') {
            $status = 'closed';
        } else if ($row['active_queues'] > 0) {
            $status = 'serving';
        }
        
        $windows[$row['id']] = [
            'id' => $row['id'],
            'window_number' => $row['window_number'],
            'status' => $status,
            'clinic' => $row['clinics'] ? explode(',', $row['clinics'])[0] : 'غير محدد',
            'active_queues' => (int)$row['active_queues']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'windows' => $windows,
        'count' => count($windows)
    ]);
    
} catch (Exception $e) {
    error_log("Get windows status error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في جلب حالة الشبابيك: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Get windows status fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في جلب حالة الشبابيك: ' . $e->getMessage()]);
}
?>