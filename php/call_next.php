<?php
session_start();
header('Content-Type: application/json');
include 'dual_db.php';

$user = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // البحث عن الدور التالي للحالة waiting لهذا الشباك
    $sql = "SELECT id, number, clinic FROM queue WHERE user_id = ? AND status = 'waiting' AND date = ? ORDER BY id DESC LIMIT 1";
    $result = $dual_db->query($sql, [$user, $today]);
    
    $row = null;
    if ($current_db_type === 'mysql') {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($row) {
        $id = $row['id'];
        $number = $row['number'];
        $clinic = $row['clinic'];
        
        // تحديث حالة الدور إلى 'called' في قاعدة البيانات النشطة
        $update_sql = "UPDATE queue SET status = 'called' WHERE id = ?";
        $dual_db->update('queue', ['status' => 'called'], 'id = ?', [$id]);
        
        // تحديث باقي الأدوار بنفس الرقم والخدمة إلى 'completed' (للأدوار المشتركة)
        $complete_sql = "UPDATE queue SET status = 'completed' WHERE number = ? AND clinic = ? AND id != ? AND status = 'waiting'";
        $dual_db->query($complete_sql, [$number, $clinic, $id]);
        
        // تسجيل النداء في السجل
        error_log("Queue called: Number $number (ID: $id) by user $user - Database: $current_db_type");
        
        // محاولة المزامنة إذا كانت قاعدتا البيانات متاحتين
        if ($dual_db->isMySQLAvailable() && $dual_db->isSQLiteAvailable()) {
            try {
                $dual_db->syncDatabases();
            } catch (Exception $e) {
                error_log("Sync failed after call: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'status' => 'success', 
            'number' => $number, 
            'id' => $id, 
            'clinic' => $clinic,
            'database' => $current_db_type
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'لا توجد أدوار في الانتظار']);
    }
    
} catch (Exception $e) {
    error_log("Call next error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'خطأ في نداء الدور التالي: ' . $e->getMessage()]);
}
?>