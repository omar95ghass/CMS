<?php
/**
 * ملف المزامنة التلقائية
 * يمكن تشغيله عبر cron job كل 5 دقائق
 */

require_once 'sync_manager.php';

// تسجيل بداية المزامنة
error_log("Starting automatic sync at " . date('Y-m-d H:i:s'));

try {
    $sync_manager = new SyncManager();
    
    // التحقق من حالة قواعد البيانات
    $status = $sync_manager->getSyncStatus();
    
    if ($status['mysql_available'] && $status['sqlite_available']) {
        // تنفيذ المزامنة
        $result = $sync_manager->performSync();
        
        if ($result['status'] === 'success') {
            error_log("Automatic sync completed successfully");
        } else {
            error_log("Automatic sync failed: " . $result['message']);
        }
    } else {
        error_log("Cannot sync: one or both databases unavailable");
    }
    
} catch (Exception $e) {
    error_log("Cron sync error: " . $e->getMessage());
}

error_log("Automatic sync finished at " . date('Y-m-d H:i:s'));
?>