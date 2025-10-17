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
    
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // جلب الأدوار مع ترتيب أفضل
    $sql = "SELECT id, number, clinic, status, created_at, date 
            FROM queue 
            WHERE user_id = ? AND date = ? 
            ORDER BY 
                CASE status 
                    WHEN 'called' THEN 1 
                    WHEN 'waiting' THEN 2 
                    WHEN 'completed' THEN 3 
                    ELSE 4 
                END, 
                id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $queueData = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // إضافة معلومات إضافية مفيدة
            $row['waiting_time'] = calculateWaitingTime($row['created_at']);
            $row['status_text'] = getStatusText($row['status']);
            $queueData[] = $row;
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $queueData]);
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Get queue error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

// دالة حساب وقت الانتظار
function calculateWaitingTime($createdAt) {
    $now = new DateTime();
    $created = new DateTime($createdAt);
    $diff = $now->diff($created);
    
    if ($diff->h > 0) {
        return $diff->h . 'س ' . $diff->i . 'د';
    } else {
        return $diff->i . 'د';
    }
}

// دالة ترجمة حالة الدور
function getStatusText($status) {
    $statuses = [
        'waiting' => 'في الانتظار',
        'called' => 'تم الطلب',
        'announced' => 'تم النداء',
        'completed' => 'مكتمل'
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : $status;
}
?>