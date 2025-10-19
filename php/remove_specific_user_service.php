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
    $clinic = $input['clinic'] ?? '';
    
    if ($user_id <= 0 || empty($clinic)) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير صحيحة']);
        exit();
    }
    
    // حذف الربط المحدد
    $stmt = $conn->prepare("DELETE FROM user_clinics WHERE user_id = ? AND clinic = ?");
    $stmt->bind_param('is', $user_id, $clinic);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'تم حذف الربط بنجاح']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'الربط غير موجود']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل في حذف الربط']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Remove specific user service error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في حذف الربط']);
}
?>