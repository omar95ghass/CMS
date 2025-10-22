<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'dual_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['window_id']) || !isset($input['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }
    
    $window_id = $input['window_id'];
    $status = $input['status'];
    
    // التحقق من صحة الحالة
    if (!in_array($status, ['available', 'closed'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }
    
    // تحديث حالة الشباك
    $sql = "UPDATE queue_users SET status = ? WHERE id = ?";
    
    if ($conn instanceof mysqli) {
        // MySQL
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $status, $window_id);
        $result = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
    } else {
        // SQLite
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$status, $window_id]);
        $affected_rows = $stmt->rowCount();
    }
    
    if ($result && $affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Window status updated successfully',
            'window_id' => $window_id,
            'status' => $status
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No rows updated'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>