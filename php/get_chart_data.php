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
    
    // بيانات توزيع الأدوار حسب الحالة
    $statusData = [
        'labels' => ['مكتملة', 'في الانتظار', 'معلنة', 'قيد الخدمة', 'محولة'],
        'values' => []
    ];
    
    $statuses = ['completed', 'waiting', 'announced', 'called', 'transferred'];
    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM queue WHERE date = CURDATE() AND status = ?");
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $statusData['values'][] = (int)$result->fetch_assoc()['count'];
        $stmt->close();
    }
    
    // بيانات الأدوار حسب الشباك
    $windowsData = [
        'labels' => [],
        'values' => []
    ];
    
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
    
    while ($row = $result->fetch_assoc()) {
        $windowsData['labels'][] = 'شباك ' . $row['window_number'];
        $windowsData['values'][] = (int)$row['count'];
    }
    $stmt->close();
    
    // بيانات الأدوار حسب الخدمة
    $clinicsData = [
        'labels' => [],
        'values' => []
    ];
    
    $stmt = $conn->prepare("
        SELECT clinic, COUNT(*) as count 
        FROM queue 
        WHERE date = CURDATE() 
        GROUP BY clinic 
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $clinicsData['labels'][] = $row['clinic'];
        $clinicsData['values'][] = (int)$row['count'];
    }
    $stmt->close();
    
    // بيانات الأدوار حسب الساعة
    $hourlyData = [
        'labels' => [],
        'values' => []
    ];
    
    $stmt = $conn->prepare("
        SELECT HOUR(created_at) as hour, COUNT(*) as count 
        FROM queue 
        WHERE date = CURDATE() 
        GROUP BY HOUR(created_at) 
        ORDER BY hour
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $hourlyData['labels'][] = $row['hour'] . ':00';
        $hourlyData['values'][] = (int)$row['count'];
    }
    $stmt->close();
    
    // بيانات الأدوار حسب اليوم (آخر 7 أيام)
    $dailyData = [
        'labels' => [],
        'values' => []
    ];
    
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM queue 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY date
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $dailyData['labels'][] = date('Y-m-d', strtotime($row['date']));
        $dailyData['values'][] = (int)$row['count'];
    }
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'status_data' => $statusData,
        'windows_data' => $windowsData,
        'clinics_data' => $clinicsData,
        'hourly_data' => $hourlyData,
        'daily_data' => $dailyData,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Get chart data error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في جلب بيانات الرسوم البيانية: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Get chart data fatal error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ فادح في جلب بيانات الرسوم البيانية: ' . $e->getMessage()]);
}
?>