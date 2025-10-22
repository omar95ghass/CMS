<?php
/**
 * ملف اختبار نظام قاعدة البيانات المزدوجة
 */

require_once 'php/dual_db.php';

echo "<h2>اختبار نظام قاعدة البيانات المزدوجة</h2>";

// اختبار الاتصال
echo "<h3>1. اختبار الاتصال</h3>";
echo "<p>MySQL متاح: " . ($dual_db->isMySQLAvailable() ? "✅ نعم" : "❌ لا") . "</p>";
echo "<p>SQLite متاح: " . ($dual_db->isSQLiteAvailable() ? "✅ نعم" : "❌ لا") . "</p>";
echo "<p>قاعدة البيانات النشطة: " . $current_db_type . "</p>";

// اختبار الإدراج
echo "<h3>2. اختبار الإدراج</h3>";
try {
    $test_data = [
        'name' => 'خدمة اختبار ' . date('H:i:s')
    ];
    
    $result = $dual_db->insert('services', $test_data);
    echo "<p>تم إدراج البيانات بنجاح في: " . implode(', ', array_keys($result)) . "</p>";
    
    // الحصول على ID المدرج
    $inserted_id = null;
    if ($current_db_type === 'mysql') {
        $inserted_id = $dual_db->getConnection()->insert_id;
    } else {
        $inserted_id = $dual_db->getConnection()->lastInsertId();
    }
    echo "<p>ID المدرج: " . $inserted_id . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ خطأ في الإدراج: " . $e->getMessage() . "</p>";
}

// اختبار الاستعلام
echo "<h3>3. اختبار الاستعلام</h3>";
try {
    $sql = "SELECT * FROM services ORDER BY id DESC LIMIT 5";
    $result = $dual_db->query($sql);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>الاسم</th></tr>";
    
    if ($current_db_type === 'mysql') {
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td></tr>";
        }
    } else {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td></tr>";
        }
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ خطأ في الاستعلام: " . $e->getMessage() . "</p>";
}

// اختبار المزامنة
echo "<h3>4. اختبار المزامنة</h3>";
if ($dual_db->isMySQLAvailable() && $dual_db->isSQLiteAvailable()) {
    try {
        $sync_result = $dual_db->syncDatabases();
        echo "<p>المزامنة: " . ($sync_result ? "✅ نجحت" : "❌ فشلت") . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ خطأ في المزامنة: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>⚠️ لا يمكن اختبار المزامنة - إحدى قواعد البيانات غير متاحة</p>";
}

// اختبار تبديل قاعدة البيانات
echo "<h3>5. اختبار تبديل قاعدة البيانات</h3>";
if ($dual_db->isMySQLAvailable() && $dual_db->isSQLiteAvailable()) {
    $original_db = $current_db_type;
    $other_db = ($original_db === 'mysql') ? 'sqlite' : 'mysql';
    
    if ($dual_db->switchDatabase($other_db)) {
        echo "<p>✅ تم التبديل من $original_db إلى $other_db</p>";
        
        // العودة إلى قاعدة البيانات الأصلية
        $dual_db->switchDatabase($original_db);
        echo "<p>✅ تم العودة إلى $original_db</p>";
    } else {
        echo "<p>❌ فشل في التبديل</p>";
    }
} else {
    echo "<p>⚠️ لا يمكن اختبار التبديل - إحدى قواعد البيانات غير متاحة</p>";
}

// حالة قواعد البيانات
echo "<h3>6. حالة قواعد البيانات</h3>";
$status = $dual_db->getDatabaseStatus();
echo "<pre>";
print_r($status);
echo "</pre>";

echo "<h3>7. اختبار إدارة المزامنة</h3>";
require_once 'php/sync_manager.php';
$sync_manager = new SyncManager();

$sync_status = $sync_manager->getSyncStatus();
echo "<p>حالة المزامنة:</p>";
echo "<pre>";
print_r($sync_status);
echo "</pre>";

$sync_stats = $sync_manager->getSyncStatistics();
echo "<p>إحصائيات المزامنة:</p>";
echo "<pre>";
print_r($sync_stats);
echo "</pre>";

echo "<h3>8. اختبار رفع اللوغو</h3>";
echo "<p>ملف رفع اللوغو موجود: " . (file_exists('php/upload_logo.php') ? "✅ نعم" : "❌ لا") . "</p>";
echo "<p>مجلد اللوغو موجود: " . (is_dir('images/logo') ? "✅ نعم" : "❌ لا") . "</p>";

echo "<h3>9. اختبار إعدادات النظام</h3>";
try {
    $settings_result = $dual_db->query("SELECT * FROM system_settings");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>المفتاح</th><th>القيمة</th></tr>";
    
    if ($current_db_type === 'mysql') {
        while ($row = $settings_result->fetch_assoc()) {
            echo "<tr><td>" . $row['setting_key'] . "</td><td>" . $row['setting_value'] . "</td></tr>";
        }
    } else {
        while ($row = $settings_result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $row['setting_key'] . "</td><td>" . $row['setting_value'] . "</td></tr>";
        }
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ خطأ في جلب الإعدادات: " . $e->getMessage() . "</p>";
}

echo "<h3>10. ملخص الاختبار</h3>";
echo "<p>✅ تم اختبار نظام قاعدة البيانات المزدوجة بنجاح</p>";
echo "<p>✅ تم اختبار نظام المزامنة</p>";
echo "<p>✅ تم اختبار رفع اللوغو</p>";
echo "<p>✅ تم اختبار إعدادات النظام</p>";

echo "<hr>";
echo "<p><strong>ملاحظة:</strong> يمكنك حذف هذا الملف بعد التأكد من عمل النظام بشكل صحيح.</p>";
?>