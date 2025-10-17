<?php
session_start();
header('Content-Type: application/json');

try {
    include 'db.php';
    
    $id = $_POST['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit();
    }
    
    // تحديث حالة الدور إلى announced
    $stmt = $conn->prepare("UPDATE queue SET status = 'announced' WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated to announced']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Mark announced error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>