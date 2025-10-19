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
    
    // التحقق من وجود الخدمة
    $stmt = $conn->prepare("SELECT id FROM services WHERE name = ?");
    $stmt->bind_param('s', $clinic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'الخدمة غير موجودة']);
        exit();
    }
    
    $stmt->close();
    
    // التحقق من عدم وجود الربط مسبقاً
    $stmt = $conn->prepare("SELECT id FROM user_clinics WHERE user_id = ? AND clinic = ?");
    $stmt->bind_param('is', $user_id, $clinic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'الربط موجود مسبقاً']);
        exit();
    }
    
    $stmt->close();
    
    // إضافة الربط
    $stmt = $conn->prepare("INSERT INTO user_clinics (user_id, clinic) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $clinic);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'تم إضافة الربط بنجاح']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل في إضافة الربط']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Add user service error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في إضافة الربط']);
}
?>