<?php
session_start();
header('Content-Type: application/json');

// إيقاف عرض الأخطاء في الإخراج
error_reporting(0);
ini_set('display_errors', 0);

try {
    include 'db.php';
    
    // التحقق من الصلاحيات
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'غير مصرح بالوصول']);
        exit();
    }
    
    // إحصائيات الأدوار
    $stats = [];
    
    // إجمالي الأدوار اليوم
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM queue WHERE date = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_queues'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // الأدوار المكتملة
    $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM queue WHERE date = CURDATE() AND status = 'completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['completed_queues'] = $result->fetch_assoc()['completed'];
    $stmt->close();
    
    // الأدوار في الانتظار
    $stmt = $conn->prepare("SELECT COUNT(*) as waiting FROM queue WHERE date = CURDATE() AND status = 'waiting'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['waiting_queues'] = $result->fetch_assoc()['waiting'];
    $stmt->close();
    
    // الأدوار المعلنة
    $stmt = $conn->prepare("SELECT COUNT(*) as announced FROM queue WHERE date = CURDATE() AND status = 'announced'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['announced_queues'] = $result->fetch_assoc()['announced'];
    $stmt->close();
    
    // الأدوار قيد الخدمة
    $stmt = $conn->prepare("SELECT COUNT(*) as called FROM queue WHERE date = CURDATE() AND status = 'called'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['called_queues'] = $result->fetch_assoc()['called'];
    $stmt->close();
    
    // متوسط مدة الخدمة
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_time 
        FROM queue 
        WHERE date = CURDATE() 
        AND status = 'completed' 
        AND updated_at > created_at
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $avgTime = $result->fetch_assoc()['avg_time'];
    $stats['avg_service_time'] = $avgTime ? round($avgTime) . ' دقيقة' : 'غير محدد';
    $stmt->close();
    
    // عدد الشبابيك النشطة
    $stmt = $conn->prepare("SELECT COUNT(*) as active_windows FROM queue_users WHERE role = 'counter' AND status = 'available'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['active_windows'] = $result->fetch_assoc()['active_windows'];
    $stmt->close();
    
    // عدد الخدمات
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT clinic) as total_clinics FROM queue WHERE date = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_clinics'] = $result->fetch_assoc()['total_clinics'];
    $stmt->close();
    
    // الأدوار المحولة
    $stmt = $conn->prepare("SELECT COUNT(*) as transferred FROM queue WHERE date = CURDATE() AND status = 'transferred'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['transferred_queues'] = $result->fetch_assoc()['transferred'];
    $stmt->close();
    
    // إحصائيات الأدوار حسب الخدمة
    $stmt = $conn->prepare("
        SELECT clinic, COUNT(*) as count 
        FROM queue 
        WHERE date = CURDATE() 
        GROUP BY clinic 
        ORDER BY count DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['queues_by_clinic'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['queues_by_clinic'][] = $row;
    }
    $stmt->close();
    
    // إحصائيات الأدوار حسب الشباك
    $stmt = $conn->prepare("
        SELECT u.window_number, COUNT(q.id) as count 
        FROM queue_users u 
        LEFT JOIN queue q ON u.id = q.user_id AND q.date = CURDATE()
        WHERE u.role = 'counter'
        GROUP BY u.id, u.window_number 
        ORDER BY u.window_number
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['queues_by_window'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['queues_by_window'][] = $row;
    }
    $stmt->close();
    
    // إحصائيات الأدوار حسب الساعة
    $stmt = $conn->prepare("
        SELECT HOUR(created_at) as hour, COUNT(*) as count 
        FROM queue 
        WHERE date = CURDATE() 
        GROUP BY HOUR(created_at) 
        ORDER BY hour
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['queues_by_hour'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['queues_by_hour'][] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Get admin stats error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في جلب الإحصائيات: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Get admin stats fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في جلب الإحصائيات: ' . $e->getMessage()]);
}
?>