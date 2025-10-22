 
<?php
// استخدام نظام قاعدة البيانات المزدوجة
require_once 'dual_db.php';

// إنشاء متغيرات متوافقة مع الكود القديم
$mysql_available = $dual_db->isMySQLAvailable();
$sqlite_available = $dual_db->isSQLiteAvailable();
$current_db_type = $dual_db->getCurrentDatabase();

// إظهار تحذير للمستخدم إذا كان يستخدم SQLite كبديل
if ($current_db_type === 'sqlite' && $mysql_available === false) {
    // يمكن إضافة إشعار للمستخدم هنا
    error_log("Using SQLite as fallback database - MySQL unavailable");
}
?>
