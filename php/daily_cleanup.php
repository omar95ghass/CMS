<?php
// سكربت تنظيف الأدوار اليومية
// يجب تشغيله يومياً عبر cron job

try {
    include 'db.php';
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // جلب الأدوار غير المكتملة من الأمس
    $stmt = $conn->prepare("
        SELECT id, number, clinic, status, created_at 
        FROM queue 
        WHERE date = ? 
        AND status IN ('waiting', 'called', 'announced')
    ");
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $incompleteQueues = [];
    while ($row = $result->fetch_assoc()) {
        $incompleteQueues[] = $row;
    }
    $stmt->close();
    
    if (!empty($incompleteQueues)) {
        // تحديث حالة الأدوار غير المكتملة إلى completed
        $stmt = $conn->prepare("
            UPDATE queue 
            SET status = 'completed' 
            WHERE date = ? 
            AND status IN ('waiting', 'called', 'announced')
        ");
        $stmt->bind_param("s", $yesterday);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        // تسجيل عملية التنظيف
        error_log("Daily cleanup completed: $affectedRows queues marked as completed for date $yesterday");
        
        // إرسال تقرير (اختياري)
        $report = [
            'date' => $today,
            'cleanup_date' => $yesterday,
            'affected_queues' => $affectedRows,
            'incomplete_queues' => $incompleteQueues
        ];
        
        // حفظ التقرير في ملف
        $reportFile = '../logs/daily_cleanup_' . $today . '.json';
        if (!is_dir('../logs')) {
            mkdir('../logs', 0755, true);
        }
        file_put_contents($reportFile, json_encode($report, JSON_UNESCAPED_UNICODE));
        
        echo json_encode([
            'status' => 'success',
            'message' => "تم تنظيف $affectedRows دور غير مكتمل من تاريخ $yesterday",
            'affected_queues' => $affectedRows
        ]);
        
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'لا توجد أدوار غير مكتملة للتنظيف',
            'affected_queues' => 0
        ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Daily cleanup error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في عملية التنظيف: ' . $e->getMessage()
    ]);
}
?>