<?php
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول والصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'غير مصرح بالوصول']);
    exit();
}

try {
    include 'db.php';
    
    $stmt = $conn->prepare("
        SELECT uc.id, uc.user_id, uc.clinic, u.username, u.window_number 
        FROM user_clinics uc 
        JOIN queue_users u ON uc.user_id = u.id 
        WHERE u.role = 'counter'
        ORDER BY u.window_number, uc.clinic
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $userServices = [];
    while ($row = $result->fetch_assoc()) {
        $userServices[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'userServices' => $userServices
    ]);
    
} catch (Exception $e) {
    error_log("Get user services error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'حدث خطأ في جلب ربط المستخدمين بالخدمات'
    ]);
}
?>