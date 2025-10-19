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
    
    // جلب جميع الخدمات
    $stmt = $conn->prepare("SELECT name FROM services");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row['name'];
    }
    
    $stmt->close();
    
    if (empty($services)) {
        echo json_encode(['status' => 'error', 'message' => 'لا توجد خدمات متاحة']);
        exit();
    }
    
    // بدء المعاملة
    $conn->begin_transaction();
    
    try {
        // حذف الربطات الموجودة
        $stmt = $conn->prepare("DELETE FROM user_clinics WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        
        // إضافة جميع الخدمات
        $stmt = $conn->prepare("INSERT INTO user_clinics (user_id, clinic) VALUES (?, ?)");
        $added_count = 0;
        
        foreach ($services as $service) {
            $stmt->bind_param('is', $user_id, $service);
            if ($stmt->execute()) {
                $added_count++;
            }
        }
        
        $stmt->close();
        
        // تأكيد المعاملة
        $conn->commit();
        
        echo json_encode([
            'status' => 'success', 
            'message' => "تم ربط المستخدم بـ $added_count خدمة بنجاح"
        ]);
        
    } catch (Exception $e) {
        // إلغاء المعاملة في حالة الخطأ
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Assign all services error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في ربط الخدمات']);
}
?>