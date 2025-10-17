<?php
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    include 'db.php';
    
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
    $validStatuses = ['waiting', 'called', 'announced', 'completed'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit();
    }
    
    // البحث عن الدور وتحديث حالته
    $stmt = $conn->prepare("SELECT id, status FROM queue WHERE user_id = ? AND number = ? AND date = ?");
    $stmt->bind_param('iis', $userId, $number, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // تحديث حالة الدور
        $updateStmt = $conn->prepare("UPDATE queue SET status = ? WHERE id = ?");
        $updateStmt->bind_param('si', $status, $row['id']);
        
        if ($updateStmt->execute()) {
            // تسجيل التغيير في السجل
            error_log("Queue status updated: Number $number changed from {$row['status']} to $status by user $userId");
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Status updated successfully',
                'number' => $number,
                'old_status' => $row['status'],
                'new_status' => $status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
        
        $updateStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Queue number not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Update status error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>