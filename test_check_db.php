<?php
session_start();

// محاكاة تسجيل الدخول
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'counter';

echo "فحص قاعدة البيانات...\n\n";

try {
    include 'php/db.php';
    
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.date = CURDATE() 
        ORDER BY q.created_at DESC
    ");
    
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "الأدوار الموجودة:\n";
    echo "ID | Number | Clinic | Status | Created At | Window\n";
    echo "---|--------|--------|--------|------------|-------\n";
    
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-3d| %-6d | %-6s | %-6s | %-10s | %-6d\n", 
            $row['id'], 
            $row['number'], 
            $row['clinic'], 
            $row['status'], 
            substr($row['created_at'], 11, 8),
            $row['window_number']
        );
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ خطأ في قاعدة البيانات: " . $e->getMessage();
}
?>