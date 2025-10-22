<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'dual_db.php';

try {
    $serving = [];
    
    // جلب الشبابيك التي تقدم خدمة حالياً
    $sql = "SELECT 
                q.id,
                q.number,
                q.clinic,
                q.user_id,
                u.window_number,
                u.window_number as window_id
            FROM queue q
            JOIN queue_users u ON q.user_id = u.id
            WHERE q.status = 'serving'
            AND q.date = CURDATE()";
    
    $result = $conn->query($sql);
    
    if ($result) {
        if ($conn instanceof mysqli) {
            // MySQL
            while ($row = $result->fetch_assoc()) {
                $serving[] = [
                    'id' => $row['id'],
                    'number' => $row['number'],
                    'clinic' => $row['clinic'],
                    'user_id' => $row['user_id'],
                    'window_number' => $row['window_number'],
                    'window_id' => $row['window_id']
                ];
            }
        } else {
            // SQLite
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $serving[] = [
                    'id' => $row['id'],
                    'number' => $row['number'],
                    'clinic' => $row['clinic'],
                    'user_id' => $row['user_id'],
                    'window_number' => $row['window_number'],
                    'window_id' => $row['window_id']
                ];
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'serving' => $serving
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>