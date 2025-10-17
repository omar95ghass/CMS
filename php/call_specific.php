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
    
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // الحصول على البيانات من JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $number = isset($input['number']) ? intval($input['number']) : 0;
    
    if ($number <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid number']);
        exit();
    }
    
    // البحث عن الدور - فقط للشباك المحدد
    $stmt = $conn->prepare("SELECT id, number, clinic, status FROM queue WHERE user_id = ? AND number = ? AND date = ? AND status = 'waiting'");
    $stmt->bind_param('iis', $userId, $number, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // تحديث حالة الدور - فقط للدور المحدد
        $updateStmt = $conn->prepare("UPDATE queue SET status = 'called' WHERE id = ?");
        $updateStmt->bind_param('i', $row['id']);
        
        if ($updateStmt->execute()) {
            // تسجيل النداء في السجل
            error_log("Queue called: Number {$row['number']} for clinic {$row['clinic']} by user $userId");
            
            echo json_encode([
                'status' => 'success', 
                'number' => $row['number'],
                'clinic' => $row['clinic'],
                'queue_id' => $row['id']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
        
        $updateStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Number not found or already called']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Call specific error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
