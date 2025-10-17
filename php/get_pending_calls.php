<?php
session_start();
header('Content-Type: application/json');

try {
    include 'db.php';
    
    $today = date('Y-m-d');
    
    // جلب النداءات التي تحتاج إلى إعلان (called أو announced)
    $stmt = $conn->prepare("
        SELECT q.id, q.number, q.clinic, q.status, q.created_at, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.date = ? 
        AND q.status IN ('called', 'announced') 
        AND q.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY q.created_at ASC
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calls = [];
    while ($row = $result->fetch_assoc()) {
        $calls[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'calls' => $calls,
        'count' => count($calls)
    ]);
    
} catch (Exception $e) {
    error_log("Get pending calls error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'calls' => []
    ]);
}
?>