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
    
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? 0;
    
    if ($user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير صحيحة']);
        exit();
    }
    
    // التحقق من وجود المستخدم
    $stmt = $conn->prepare("SELECT id FROM queue_users WHERE id = ? AND role = 'counter'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'المستخدم غير موجود أو ليس counter']);
        exit();
    }
    
    $stmt->close();
    
    // حذف جميع ربطات المستخدم
    $stmt = $conn->prepare("DELETE FROM user_clinics WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        $deleted_count = $stmt->affected_rows;
        echo json_encode([
            'status' => 'success', 
            'message' => "تم حذف $deleted_count ربط بنجاح"
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل في حذف الربطات']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Remove user services error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في حذف الربطات']);
}
?>