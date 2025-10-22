<?php
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    include 'dual_db.php';
    
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // الحصول على البيانات من JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $number = isset($input['number']) ? intval($input['number']) : 0;
    
    if ($number <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'رقم غير صحيح']);
        exit();
    }
    
    // البحث عن الدور - فقط للشباك المحدد
    $sql = "SELECT id, number, clinic, status FROM queue WHERE user_id = ? AND number = ? AND date = ? AND status = 'waiting'";
    $result = $dual_db->query($sql, [$userId, $number, $today]);
    
    $row = null;
    if ($current_db_type === 'mysql') {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($row) {
        // تحديث حالة الدور إلى 'called' في قاعدة البيانات النشطة
        $dual_db->update('queue', ['status' => 'called'], 'id = ?', [$row['id']]);
        
        // تحديث باقي الأدوار بنفس الرقم والخدمة إلى 'completed' (للأدوار المشتركة)
        $complete_sql = "UPDATE queue SET status = 'completed' WHERE number = ? AND clinic = ? AND id != ? AND status = 'waiting'";
        $dual_db->query($complete_sql, [$row['number'], $row['clinic'], $row['id']]);
        
        // تسجيل النداء في السجل
        error_log("Queue called: Number {$row['number']} for clinic {$row['clinic']} by user $userId - Database: $current_db_type");
        
        // محاولة المزامنة إذا كانت قاعدتا البيانات متاحتين
        if ($dual_db->isMySQLAvailable() && $dual_db->isSQLiteAvailable()) {
            try {
                $dual_db->syncDatabases();
            } catch (Exception $e) {
                error_log("Sync failed after specific call: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'status' => 'success', 
            'number' => $row['number'],
            'clinic' => $row['clinic'],
            'queue_id' => $row['id'],
            'database' => $current_db_type
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'رقم الدور غير موجود أو تم نداؤه مسبقاً']);
    }
    
} catch (Exception $e) {
    error_log("Call specific error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>
