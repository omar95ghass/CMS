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
    
    $today = date('Y-m-d');
    $userId = $_SESSION['user_id'];
    
    // جلب النداءات الخاصة بالشباك المحدد فقط
    $stmt = $conn->prepare("
        SELECT q.id, q.number, q.clinic, q.status, q.created_at, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? 
        AND q.date = ? 
        AND q.status IN ('called', 'announced') 
        AND q.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY q.created_at ASC
    ");
    $stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calls = [];
    while ($row = $result->fetch_assoc()) {
        $calls[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'calls' => $calls,
        'count' => count($calls),
        'window_number' => $_SESSION['window_number'] ?? 'غير محدد'
    ]);
    
} catch (Exception $e) {
    error_log("Get window calls error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في جلب نداءات الشباك',
        'calls' => []
    ]);
}
?>