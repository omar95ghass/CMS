<?php
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
//     exit();
// }

try {
    include 'dual_db.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $number = isset($input['number']) ? intval($input['number']) : 0;
    $status = isset($input['status']) ? $input['status'] : '';
    $date = isset($input['date']) ? $input['date'] : date('Y-m-d');
    $userId = $_SESSION['user_id'];
    
    if ($number <= 0 || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit();
    }
    
    // التحقق من صحة الحالة
    $validStatuses = ['waiting', 'called', 'serving', 'announced', 'completed'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit();
    }
    
    // البحث عن الدور
    $sql = "SELECT id, status FROM queue WHERE user_id = ? AND number = ? AND date = ?";
    $result = $dual_db->query($sql, [$userId, $number, $date]);
    
    $row = null;
    if ($current_db_type === 'mysql') {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($row) {
        $queueId = $row['id'];
        $oldStatus = $row['status'];
        
        // تحضير بيانات التحديث
        $updateData = ['status' => $status];
        if ($status === 'completed') {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // تحديث الحالة في قاعدة البيانات النشطة
        $dual_db->update('queue', $updateData, 'id = ?', [$queueId]);
        
        // تسجيل التغيير في السجل
        error_log("Queue status updated: Number $number changed from $oldStatus to $status by user $userId - Database: $current_db_type");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'number' => $number,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'updated_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
            'database' => $current_db_type
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Queue number not found']);
    }
    
} catch (Exception $e) {
    error_log("Update status error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>
