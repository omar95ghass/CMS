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
    $clinic = $input['clinic'] ?? '';
    $currentUserId = $_SESSION['user_id'];
    
    if (empty($clinic)) {
        echo json_encode(['status' => 'error', 'message' => 'اسم الخدمة مطلوب']);
        exit();
    }
    
    // جلب الشبابيك المتاحة التي تخدم نفس الخدمة
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.username, u.window_number, u.role
        FROM queue_users u 
        JOIN user_clinics uc ON u.id = uc.user_id 
        WHERE uc.clinic = ? 
        AND u.id != ? 
        AND u.role = 'counter'
        AND u.window_number IS NOT NULL
        ORDER BY u.window_number ASC
    ");
    $stmt->bind_param('si', $clinic, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $windows = [];
    while ($row = $result->fetch_assoc()) {
        $windows[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'window_number' => $row['window_number'],
            'display_name' => 'الشباك ' . $row['window_number'] . ' - ' . $row['username']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'windows' => $windows,
        'count' => count($windows)
    ]);
    
} catch (Exception $e) {
    error_log("Get available windows error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في جلب الشبابيك المتاحة',
        'windows' => []
    ]);
}
?>