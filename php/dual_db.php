<?php
/**
 * نظام قاعدة البيانات المزدوجة مع Failover
 * يدعم MySQL و SQLite مع آلية المزامنة
 */

class DualDatabase {
    private $mysql_conn = null;
    private $sqlite_conn = null;
    private $current_db = 'mysql'; // mysql أو sqlite
    private $mysql_available = false;
    private $sqlite_available = false;
    private $last_sync_time = null;
    private $log_file = '../logs/db_errors.log';
    
    public function __construct() {
        $this->initializeConnections();
    }
    
    /**
     * تهيئة الاتصالات بقاعدتي البيانات
     */
    private function initializeConnections() {
        // تهيئة MySQL
        $this->initMySQL();
        
        // تهيئة SQLite
        $this->initSQLite();
        
        // تحديد قاعدة البيانات النشطة
        $this->determineActiveDatabase();
    }
    
    /**
     * تهيئة اتصال MySQL
     */
    private function initMySQL() {
        try {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "queue_db";
            
            $this->mysql_conn = new mysqli($servername, $username, $password, $dbname);
            
            if ($this->mysql_conn->connect_error) {
                throw new Exception("MySQL connection failed: " . $this->mysql_conn->connect_error);
            }
            
            $this->mysql_conn->set_charset("utf8mb4");
            $this->mysql_conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
            $this->mysql_available = true;
            $this->log("MySQL connection established successfully");
            
        } catch (Exception $e) {
            $this->mysql_available = false;
            $this->log("MySQL connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * تهيئة اتصال SQLite
     */
    private function initSQLite() {
        try {
            $db_path = '../database/queue_db.sqlite';
            
            // إنشاء مجلد قاعدة البيانات إذا لم يكن موجوداً
            $db_dir = dirname($db_path);
            if (!is_dir($db_dir)) {
                mkdir($db_dir, 0755, true);
            }
            
            $this->sqlite_conn = new PDO("sqlite:$db_path");
            $this->sqlite_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->sqlite_conn->exec("PRAGMA foreign_keys = ON");
            
            $this->sqlite_available = true;
            $this->log("SQLite connection established successfully");
            
            // إنشاء الجداول إذا لم تكن موجودة
            $this->createSQLiteTables();
            
        } catch (Exception $e) {
            $this->sqlite_available = false;
            $this->log("SQLite connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * إنشاء جداول SQLite مطابقة لـ MySQL
     */
    private function createSQLiteTables() {
        if (!$this->sqlite_available) return;
        
        $tables = [
            "CREATE TABLE IF NOT EXISTS screens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                screen_number INTEGER UNIQUE NOT NULL,
                ip TEXT NOT NULL,
                port INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS queue_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT CHECK(role IN ('admin','counter','cashier')) NOT NULL DEFAULT 'counter',
                window_number INTEGER,
                status TEXT CHECK(status IN ('available','closed')) NOT NULL DEFAULT 'available',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                assigned_screen INTEGER,
                FOREIGN KEY (assigned_screen) REFERENCES screens(id) ON DELETE SET NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS user_clinics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                clinic TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES queue_users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                clinic TEXT NOT NULL,
                number INTEGER NOT NULL,
                status TEXT CHECK(status IN ('waiting','called','serving','announced','completed','transferred')) NOT NULL DEFAULT 'waiting',
                date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME,
                transferred_from INTEGER,
                transferred_to INTEGER,
                transferred_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES queue_users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS error_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                error_type TEXT NOT NULL,
                error_message TEXT NOT NULL,
                user_agent TEXT,
                ip_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS system_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT UNIQUE NOT NULL,
                setting_value TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS queue_transfers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                original_queue_id INTEGER NOT NULL,
                new_queue_id INTEGER NOT NULL,
                from_user_id INTEGER NOT NULL,
                to_user_id INTEGER NOT NULL,
                clinic TEXT NOT NULL,
                number INTEGER NOT NULL,
                transferred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (from_user_id) REFERENCES queue_users(id) ON DELETE CASCADE,
                FOREIGN KEY (to_user_id) REFERENCES queue_users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS db_sync_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sync_type TEXT NOT NULL,
                table_name TEXT NOT NULL,
                record_id INTEGER,
                action TEXT NOT NULL,
                sync_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'pending'
            )"
        ];
        
        foreach ($tables as $sql) {
            try {
                $this->sqlite_conn->exec($sql);
            } catch (Exception $e) {
                $this->log("Error creating SQLite table: " . $e->getMessage());
            }
        }
        
        // إنشاء فهارس
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_queue_clinic_number_date ON queue(clinic, number, date)",
            "CREATE INDEX IF NOT EXISTS idx_queue_status_date ON queue(status, date)",
            "CREATE INDEX IF NOT EXISTS idx_error_type ON error_logs(error_type)",
            "CREATE INDEX IF NOT EXISTS idx_error_created_at ON error_logs(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_sync_table ON db_sync_log(table_name)",
            "CREATE INDEX IF NOT EXISTS idx_sync_status ON db_sync_log(status)"
        ];
        
        foreach ($indexes as $sql) {
            try {
                $this->sqlite_conn->exec($sql);
            } catch (Exception $e) {
                $this->log("Error creating SQLite index: " . $e->getMessage());
            }
        }
    }
    
    /**
     * تحديد قاعدة البيانات النشطة
     */
    private function determineActiveDatabase() {
        if ($this->mysql_available) {
            $this->current_db = 'mysql';
            $this->log("Using MySQL as primary database");
        } elseif ($this->sqlite_available) {
            $this->current_db = 'sqlite';
            $this->log("MySQL unavailable, using SQLite as fallback");
        } else {
            throw new Exception("No database connection available");
        }
    }
    
    /**
     * الحصول على الاتصال النشط
     */
    public function getConnection() {
        if ($this->current_db === 'mysql' && $this->mysql_available) {
            return $this->mysql_conn;
        } elseif ($this->current_db === 'sqlite' && $this->sqlite_available) {
            return $this->sqlite_conn;
        } else {
            throw new Exception("No active database connection");
        }
    }
    
    /**
     * الحصول على نوع قاعدة البيانات النشطة
     */
    public function getCurrentDatabase() {
        return $this->current_db;
    }
    
    /**
     * التحقق من توفر MySQL
     */
    public function isMySQLAvailable() {
        return $this->mysql_available;
    }
    
    /**
     * التحقق من توفر SQLite
     */
    public function isSQLiteAvailable() {
        return $this->sqlite_available;
    }
    
    /**
     * تبديل إلى قاعدة بيانات أخرى
     */
    public function switchDatabase($db_type) {
        if ($db_type === 'mysql' && $this->mysql_available) {
            $this->current_db = 'mysql';
            $this->log("Switched to MySQL database");
            return true;
        } elseif ($db_type === 'sqlite' && $this->sqlite_available) {
            $this->current_db = 'sqlite';
            $this->log("Switched to SQLite database");
            return true;
        }
        return false;
    }
    
    /**
     * تنفيذ استعلام على قاعدة البيانات النشطة
     */
    public function query($sql, $params = []) {
        try {
            $conn = $this->getConnection();
            
            if ($this->current_db === 'mysql') {
                if (!empty($params)) {
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $types = str_repeat('s', count($params));
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        return $stmt->get_result();
                    }
                } else {
                    return $conn->query($sql);
                }
            } else {
                // SQLite
                if (!empty($params)) {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    return $stmt;
                } else {
                    return $conn->query($sql);
                }
            }
        } catch (Exception $e) {
            $this->log("Query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * إدراج بيانات في كلا قاعدتي البيانات
     */
    public function insert($table, $data) {
        $results = [];
        
        // إدراج في قاعدة البيانات النشطة
        try {
            $result = $this->insertToDatabase($table, $data, $this->current_db);
            $results[$this->current_db] = $result;
            
            // إدراج في قاعدة البيانات الأخرى إذا كانت متاحة
            $other_db = ($this->current_db === 'mysql') ? 'sqlite' : 'mysql';
            if ($this->isDatabaseAvailable($other_db)) {
                try {
                    $other_result = $this->insertToDatabase($table, $data, $other_db);
                    $results[$other_db] = $other_result;
                } catch (Exception $e) {
                    $this->log("Failed to insert to $other_db: " . $e->getMessage());
                    $this->logSyncError($table, $data, 'insert', $other_db);
                }
            }
            
        } catch (Exception $e) {
            $this->log("Insert failed on primary database: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * تحديث بيانات في كلا قاعدتي البيانات
     */
    public function update($table, $data, $where, $where_params = []) {
        $results = [];
        
        // تحديث في قاعدة البيانات النشطة
        try {
            $result = $this->updateInDatabase($table, $data, $where, $where_params, $this->current_db);
            $results[$this->current_db] = $result;
            
            // تحديث في قاعدة البيانات الأخرى إذا كانت متاحة
            $other_db = ($this->current_db === 'mysql') ? 'sqlite' : 'mysql';
            if ($this->isDatabaseAvailable($other_db)) {
                try {
                    $other_result = $this->updateInDatabase($table, $data, $where, $where_params, $other_db);
                    $results[$other_db] = $other_result;
                } catch (Exception $e) {
                    $this->log("Failed to update $other_db: " . $e->getMessage());
                    $this->logSyncError($table, $data, 'update', $other_db);
                }
            }
            
        } catch (Exception $e) {
            $this->log("Update failed on primary database: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * حذف بيانات من كلا قاعدتي البيانات
     */
    public function delete($table, $where, $where_params = []) {
        $results = [];
        
        // حذف من قاعدة البيانات النشطة
        try {
            $result = $this->deleteFromDatabase($table, $where, $where_params, $this->current_db);
            $results[$this->current_db] = $result;
            
            // حذف من قاعدة البيانات الأخرى إذا كانت متاحة
            $other_db = ($this->current_db === 'mysql') ? 'sqlite' : 'mysql';
            if ($this->isDatabaseAvailable($other_db)) {
                try {
                    $other_result = $this->deleteFromDatabase($table, $where, $where_params, $other_db);
                    $results[$other_db] = $other_result;
                } catch (Exception $e) {
                    $this->log("Failed to delete from $other_db: " . $e->getMessage());
                    $this->logSyncError($table, $where, 'delete', $other_db);
                }
            }
            
        } catch (Exception $e) {
            $this->log("Delete failed on primary database: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * إدراج بيانات في قاعدة بيانات محددة
     */
    private function insertToDatabase($table, $data, $db_type) {
        $conn = $this->getConnectionForDatabase($db_type);
        
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        if ($db_type === 'mysql') {
            $stmt = $conn->prepare($sql);
            $types = str_repeat('s', count($data));
            $stmt->bind_param($types, ...array_values($data));
            $stmt->execute();
            return $conn->insert_id;
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return $conn->lastInsertId();
        }
    }
    
    /**
     * تحديث بيانات في قاعدة بيانات محددة
     */
    private function updateInDatabase($table, $data, $where, $where_params, $db_type) {
        $conn = $this->getConnectionForDatabase($db_type);
        
        $set_clause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE $table SET $set_clause WHERE $where";
        
        $params = array_merge(array_values($data), $where_params);
        
        if ($db_type === 'mysql') {
            $stmt = $conn->prepare($sql);
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->affected_rows;
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        }
    }
    
    /**
     * حذف بيانات من قاعدة بيانات محددة
     */
    private function deleteFromDatabase($table, $where, $where_params, $db_type) {
        $conn = $this->getConnectionForDatabase($db_type);
        
        $sql = "DELETE FROM $table WHERE $where";
        
        if ($db_type === 'mysql') {
            $stmt = $conn->prepare($sql);
            if (!empty($where_params)) {
                $types = str_repeat('s', count($where_params));
                $stmt->bind_param($types, ...$where_params);
            }
            $stmt->execute();
            return $stmt->affected_rows;
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute($where_params);
            return $stmt->rowCount();
        }
    }
    
    /**
     * الحصول على اتصال قاعدة بيانات محددة
     */
    private function getConnectionForDatabase($db_type) {
        if ($db_type === 'mysql' && $this->mysql_available) {
            return $this->mysql_conn;
        } elseif ($db_type === 'sqlite' && $this->sqlite_available) {
            return $this->sqlite_conn;
        } else {
            throw new Exception("Database $db_type not available");
        }
    }
    
    /**
     * التحقق من توفر قاعدة بيانات
     */
    private function isDatabaseAvailable($db_type) {
        return ($db_type === 'mysql' && $this->mysql_available) || 
               ($db_type === 'sqlite' && $this->sqlite_available);
    }
    
    /**
     * تسجيل خطأ المزامنة
     */
    private function logSyncError($table, $data, $action, $db_type) {
        $sync_data = [
            'sync_type' => $action,
            'table_name' => $table,
            'record_data' => json_encode($data),
            'target_db' => $db_type,
            'status' => 'failed'
        ];
        
        try {
            $this->insertToDatabase('db_sync_log', $sync_data, $this->current_db);
        } catch (Exception $e) {
            $this->log("Failed to log sync error: " . $e->getMessage());
        }
    }
    
    /**
     * مزامنة البيانات بين قاعدتي البيانات
     */
    public function syncDatabases() {
        if (!$this->mysql_available || !$this->sqlite_available) {
            $this->log("Cannot sync: one or both databases unavailable");
            return false;
        }
        
        try {
            // مزامنة من MySQL إلى SQLite
            $this->syncFromMySQLToSQLite();
            
            // مزامنة من SQLite إلى MySQL
            $this->syncFromSQLiteToMySQL();
            
            $this->last_sync_time = date('Y-m-d H:i:s');
            $this->log("Database sync completed successfully");
            return true;
            
        } catch (Exception $e) {
            $this->log("Database sync failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * مزامنة من MySQL إلى SQLite
     */
    private function syncFromMySQLToSQLite() {
        $tables = ['screens', 'services', 'queue_users', 'user_clinics', 'queue', 'system_settings'];
        
        foreach ($tables as $table) {
            try {
                // جلب البيانات من MySQL
                $result = $this->mysql_conn->query("SELECT * FROM $table");
                
                while ($row = $result->fetch_assoc()) {
                    // حذف السجل إذا كان موجوداً في SQLite
                    $this->sqlite_conn->prepare("DELETE FROM $table WHERE id = ?")->execute([$row['id']]);
                    
                    // إدراج السجل الجديد
                    $this->insertToDatabase($table, $row, 'sqlite');
                }
                
            } catch (Exception $e) {
                $this->log("Error syncing table $table from MySQL to SQLite: " . $e->getMessage());
            }
        }
    }
    
    /**
     * مزامنة من SQLite إلى MySQL
     */
    private function syncFromSQLiteToMySQL() {
        $tables = ['screens', 'services', 'queue_users', 'user_clinics', 'queue', 'system_settings'];
        
        foreach ($tables as $table) {
            try {
                // جلب البيانات من SQLite
                $result = $this->sqlite_conn->query("SELECT * FROM $table");
                
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    // حذف السجل إذا كان موجوداً في MySQL
                    $stmt = $this->mysql_conn->prepare("DELETE FROM $table WHERE id = ?");
                    $stmt->bind_param('i', $row['id']);
                    $stmt->execute();
                    
                    // إدراج السجل الجديد
                    $this->insertToDatabase($table, $row, 'mysql');
                }
                
            } catch (Exception $e) {
                $this->log("Error syncing table $table from SQLite to MySQL: " . $e->getMessage());
            }
        }
    }
    
    /**
     * الحصول على حالة قواعد البيانات
     */
    public function getDatabaseStatus() {
        return [
            'mysql_available' => $this->mysql_available,
            'sqlite_available' => $this->sqlite_available,
            'current_database' => $this->current_db,
            'last_sync_time' => $this->last_sync_time,
            'sync_needed' => $this->isSyncNeeded()
        ];
    }
    
    /**
     * التحقق من الحاجة للمزامنة
     */
    private function isSyncNeeded() {
        if (!$this->mysql_available || !$this->sqlite_available) {
            return false;
        }
        
        // التحقق من وجود سجلات في جدول المزامنة
        try {
            $result = $this->query("SELECT COUNT(*) as count FROM db_sync_log WHERE status = 'pending'");
            if ($this->current_db === 'mysql') {
                $row = $result->fetch_assoc();
            } else {
                $row = $result->fetch(PDO::FETCH_ASSOC);
            }
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * تسجيل رسالة في ملف السجل
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message" . PHP_EOL;
        
        // إنشاء مجلد السجلات إذا لم يكن موجوداً
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * إغلاق الاتصالات
     */
    public function close() {
        if ($this->mysql_conn) {
            $this->mysql_conn->close();
        }
        if ($this->sqlite_conn) {
            $this->sqlite_conn = null;
        }
    }
}

// إنشاء مثيل عام للاستخدام في الملفات الأخرى
$dual_db = new DualDatabase();
$conn = $dual_db->getConnection();
$current_db_type = $dual_db->getCurrentDatabase();
?>