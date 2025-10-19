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
    
    $stmt = $conn->prepare("SELECT id, name FROM services ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'services' => $services
    ]);
    
} catch (Exception $e) {
    error_log("Get services error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'حدث خطأ في جلب الخدمات'
    ]);
}
?>