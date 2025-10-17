<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit();
}

try {
    // إنشاء مجلد logs إذا لم يكن موجوداً
    $logs_dir = '../logs';
    if (!is_dir($logs_dir)) {
        mkdir($logs_dir, 0755, true);
    }
    
    // إعداد بيانات الخطأ
    $error_data = [
        'timestamp' => $input['timestamp'] ?? date('Y-m-d H:i:s'),
        'type' => $input['type'] ?? 'unknown',
        'message' => $input['message'] ?? 'No message provided',
        'user_agent' => $input['userAgent'] ?? 'Unknown',
        'url' => $input['url'] ?? 'Unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'session_id' => session_id() ?: 'No session'
    ];
    
    // كتابة الخطأ في ملف السجل
    $log_file = $logs_dir . '/errors_' . date('Y-m-d') . '.log';
    $log_entry = date('Y-m-d H:i:s') . " - " . json_encode($error_data, JSON_UNESCAPED_UNICODE) . "\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // أيضاً تسجيل في قاعدة البيانات إذا كانت متاحة
    try {
        include 'db.php';
        
        $stmt = $conn->prepare("INSERT INTO error_logs (error_type, error_message, user_agent, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", 
            $error_data['type'], 
            $error_data['message'], 
            $error_data['user_agent'], 
            $error_data['ip']
        );
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // إذا فشل تسجيل قاعدة البيانات، لا نريد أن نفشل العملية
        error_log("Failed to log error to database: " . $e->getMessage());
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Error logged successfully']);
    
} catch (Exception $e) {
    error_log("Error logging failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to log error']);
}
?>