<?php
/**
 * مدير المزامنة بين قاعدتي البيانات
 */

require_once 'dual_db.php';

class SyncManager {
    private $dual_db;
    
    public function __construct() {
        $this->dual_db = new DualDatabase();
    }
    
    /**
     * مزامنة فورية بين قاعدتي البيانات
     */
    public function performSync() {
        try {
            $result = $this->dual_db->syncDatabases();
            
            if ($result) {
                return [
                    'status' => 'success',
                    'message' => 'تمت المزامنة بنجاح',
                    'sync_time' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'فشلت المزامنة'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'خطأ في المزامنة: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * الحصول على حالة المزامنة
     */
    public function getSyncStatus() {
        return $this->dual_db->getDatabaseStatus();
    }
    
    /**
     * مزامنة جدول محدد
     */
    public function syncTable($table_name) {
        try {
            if (!$this->dual_db->isMySQLAvailable() || !$this->dual_db->isSQLiteAvailable()) {
                return [
                    'status' => 'error',
                    'message' => 'إحدى قواعد البيانات غير متاحة'
                ];
            }
            
            // مزامنة من MySQL إلى SQLite
            $this->syncTableFromMySQLToSQLite($table_name);
            
            // مزامنة من SQLite إلى MySQL
            $this->syncTableFromSQLiteToMySQL($table_name);
            
            return [
                'status' => 'success',
                'message' => "تمت مزامنة جدول $table_name بنجاح"
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'خطأ في مزامنة الجدول: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * مزامنة جدول من MySQL إلى SQLite
     */
    private function syncTableFromMySQLToSQLite($table_name) {
        $mysql_conn = $this->dual_db->getConnection();
        
        if ($this->dual_db->getCurrentDatabase() !== 'mysql') {
            // إعادة الاتصال بـ MySQL
            $this->dual_db->switchDatabase('mysql');
            $mysql_conn = $this->dual_db->getConnection();
        }
        
        $result = $mysql_conn->query("SELECT * FROM $table_name");
        
        while ($row = $result->fetch_assoc()) {
            // حذف السجل إذا كان موجوداً في SQLite
            $this->dual_db->query("DELETE FROM $table_name WHERE id = ?", [$row['id']], 'sqlite');
            
            // إدراج السجل الجديد
            $this->dual_db->insertToDatabase($table_name, $row, 'sqlite');
        }
    }
    
    /**
     * مزامنة جدول من SQLite إلى MySQL
     */
    private function syncTableFromSQLiteToMySQL($table_name) {
        $sqlite_conn = $this->dual_db->getConnection();
        
        if ($this->dual_db->getCurrentDatabase() !== 'sqlite') {
            // إعادة الاتصال بـ SQLite
            $this->dual_db->switchDatabase('sqlite');
            $sqlite_conn = $this->dual_db->getConnection();
        }
        
        $result = $sqlite_conn->query("SELECT * FROM $table_name");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // حذف السجل إذا كان موجوداً في MySQL
            $this->dual_db->query("DELETE FROM $table_name WHERE id = ?", [$row['id']], 'mysql');
            
            // إدراج السجل الجديد
            $this->dual_db->insertToDatabase($table_name, $row, 'mysql');
        }
    }
    
    /**
     * إصلاح عدم التطابق في البيانات
     */
    public function repairDataMismatch() {
        try {
            $tables = ['screens', 'services', 'queue_users', 'user_clinics', 'queue', 'system_settings'];
            $repair_results = [];
            
            foreach ($tables as $table) {
                $result = $this->repairTable($table);
                $repair_results[$table] = $result;
            }
            
            return [
                'status' => 'success',
                'message' => 'تم إصلاح عدم التطابق في البيانات',
                'results' => $repair_results
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'خطأ في إصلاح البيانات: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * إصلاح جدول محدد
     */
    private function repairTable($table_name) {
        $mysql_data = [];
        $sqlite_data = [];
        
        // جلب البيانات من MySQL
        if ($this->dual_db->isMySQLAvailable()) {
            $mysql_conn = $this->dual_db->getConnection();
            $result = $mysql_conn->query("SELECT * FROM $table_name");
            while ($row = $result->fetch_assoc()) {
                $mysql_data[$row['id']] = $row;
            }
        }
        
        // جلب البيانات من SQLite
        if ($this->dual_db->isSQLiteAvailable()) {
            $sqlite_conn = $this->dual_db->getConnection();
            $result = $sqlite_conn->query("SELECT * FROM $table_name");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $sqlite_data[$row['id']] = $row;
            }
        }
        
        $repair_count = 0;
        
        // إصلاح البيانات المفقودة في SQLite
        foreach ($mysql_data as $id => $row) {
            if (!isset($sqlite_data[$id])) {
                $this->dual_db->insertToDatabase($table_name, $row, 'sqlite');
                $repair_count++;
            }
        }
        
        // إصلاح البيانات المفقودة في MySQL
        foreach ($sqlite_data as $id => $row) {
            if (!isset($mysql_data[$id])) {
                $this->dual_db->insertToDatabase($table_name, $row, 'mysql');
                $repair_count++;
            }
        }
        
        return [
            'table' => $table_name,
            'repair_count' => $repair_count,
            'mysql_records' => count($mysql_data),
            'sqlite_records' => count($sqlite_data)
        ];
    }
    
    /**
     * الحصول على إحصائيات المزامنة
     */
    public function getSyncStatistics() {
        try {
            $stats = [];
            
            if ($this->dual_db->isMySQLAvailable()) {
                $mysql_conn = $this->dual_db->getConnection();
                $result = $mysql_conn->query("
                    SELECT 
                        'mysql' as database_type,
                        (SELECT COUNT(*) FROM screens) as screens_count,
                        (SELECT COUNT(*) FROM services) as services_count,
                        (SELECT COUNT(*) FROM queue_users) as users_count,
                        (SELECT COUNT(*) FROM queue) as queue_count,
                        (SELECT COUNT(*) FROM system_settings) as settings_count
                ");
                $stats['mysql'] = $result->fetch_assoc();
            }
            
            if ($this->dual_db->isSQLiteAvailable()) {
                $sqlite_conn = $this->dual_db->getConnection();
                $result = $sqlite_conn->query("
                    SELECT 
                        'sqlite' as database_type,
                        (SELECT COUNT(*) FROM screens) as screens_count,
                        (SELECT COUNT(*) FROM services) as services_count,
                        (SELECT COUNT(*) FROM queue_users) as users_count,
                        (SELECT COUNT(*) FROM queue) as queue_count,
                        (SELECT COUNT(*) FROM system_settings) as settings_count
                ");
                $stats['sqlite'] = $result->fetch(PDO::FETCH_ASSOC);
            }
            
            return [
                'status' => 'success',
                'statistics' => $stats,
                'sync_needed' => $this->dual_db->getDatabaseStatus()['sync_needed']
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'خطأ في جلب الإحصائيات: ' . $e->getMessage()
            ];
        }
    }
}

// API endpoints للمزامنة
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $sync_manager = new SyncManager();
    
    switch ($_GET['action']) {
        case 'sync':
            echo json_encode($sync_manager->performSync());
            break;
            
        case 'status':
            echo json_encode($sync_manager->getSyncStatus());
            break;
            
        case 'sync_table':
            if (isset($_GET['table'])) {
                echo json_encode($sync_manager->syncTable($_GET['table']));
            } else {
                echo json_encode(['status' => 'error', 'message' => 'اسم الجدول مطلوب']);
            }
            break;
            
        case 'repair':
            echo json_encode($sync_manager->repairDataMismatch());
            break;
            
        case 'statistics':
            echo json_encode($sync_manager->getSyncStatistics());
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'إجراء غير صحيح']);
    }
}
?>