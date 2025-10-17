<?php
// setup.php  
// هذا السكربت يتحقق وينشئ قاعدة البيانات والجداول إذا لم تكن موجودة

// إعدادات الاتصال بالـ MySQL (بدون تحديد قاعدة بيانات أولاً)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'queue_db';

// افتح اتصال عام
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("فشل الاتصال بخادم MySQL: " . $conn->connect_error);
}

// تأكد من ترميز الاتصال
$conn->set_charset('utf8mb4');

// 1) إنشاء قاعدة البيانات إذا لم تكن موجودة
if (!$conn->select_db($db)) {
    $sql = "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$conn->query($sql)) {
        die("فشل في إنشاء قاعدة البيانات `$db`: " . $conn->error);
    }
    // echo "✔ تم إنشاء قاعدة البيانات `$db`\n";
    $conn->select_db($db);
} else {
    echo "✔ قاعدة البيانات `$db` موجودة مسبقاً\n";
}

// 2) إنشاء الجداول إذا لم تكن موجودة

$tables = [

    "CREATE TABLE IF NOT EXISTS `screens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `screen_number` tinyint(3) unsigned NOT NULL COMMENT 'رقم الشاشة (1،2،3...)',
    `ip` varchar(45) NOT NULL COMMENT 'IP الخاص بالشاشة',
    `port` smallint(5) unsigned NOT NULL COMMENT 'المنفذ (port)',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `screen_number` (`screen_number`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `queue_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','counter','cashier') NOT NULL DEFAULT 'counter',
    `window_number` tinyint(3) unsigned DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `assigned_screen` int(11) DEFAULT NULL COMMENT 'الشاشة/الشباك المربوطة بالمستخدم',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    KEY `assigned_screen` (`assigned_screen`),
    CONSTRAINT `queue_users_ibfk_1` FOREIGN KEY (`assigned_screen`) REFERENCES `screens` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `user_clinics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `clinic` varchar(100) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_user_clinics_user` (`user_id`),
    CONSTRAINT `fk_user_clinics_user` FOREIGN KEY (`user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `clinic` varchar(100) NOT NULL,
    `number` int(11) NOT NULL,
    `status` enum('waiting','called','announced','completed','transferred') NOT NULL DEFAULT 'waiting',
    `date` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `transferred_from` int(11) DEFAULT NULL COMMENT 'الشباك الذي تم التحويل منه',
    `transferred_to` int(11) DEFAULT NULL COMMENT 'الشباك الذي تم التحويل إليه',
    `transferred_at` timestamp NULL DEFAULT NULL COMMENT 'وقت التحويل',
    PRIMARY KEY (`id`),
    KEY `fk_user_queue` (`user_id`),
    KEY `idx_clinic_number_date` (`clinic`, `number`, `date`),
    KEY `idx_status_date` (`status`, `date`),
    CONSTRAINT `fk_user_queue` FOREIGN KEY (`user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `error_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `error_type` varchar(50) NOT NULL,
    `error_message` text NOT NULL,
    `user_agent` text,
    `ip_address` varchar(45),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_error_type` (`error_type`),
    KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `queue_transfers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `original_queue_id` int(11) NOT NULL,
    `new_queue_id` int(11) NOT NULL,
    `from_user_id` int(11) NOT NULL,
    `to_user_id` int(11) NOT NULL,
    `clinic` varchar(100) NOT NULL,
    `number` int(11) NOT NULL,
    `transferred_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_from_user` (`from_user_id`),
    KEY `idx_to_user` (`to_user_id`),
    KEY `idx_transferred_at` (`transferred_at`),
    CONSTRAINT `fk_transfer_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_transfer_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
];

// ننفذ كل جملة
foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("فشل في إنشاء الجدول: " . $conn->error . "\nSQL: $sql\n");
    }
    // استخراج اسم الجدول من SQL للإخراج
    if (preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $sql, $m)) {
        // echo "✔ الجدول `{$m[1]}` جاهز\n";
    }
}

$username = 'admin';
// لأغراض الأمان، من الضروري تشفير كلمة المرور
$password = 'admin';
$role = 'admin';

$sql_check = "SELECT id FROM queue_users WHERE username = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    $sql_insert = "INSERT INTO queue_users (username, password, role) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sss", $username, $password, $role);
    
    if ($stmt_insert->execute()) {
        // echo "تم إنشاء المستخدم admin بنجاح.";
    } else {
        echo "Error creating admin user" . $stmt_insert->error;
    }
    
    $stmt_insert->close();
} else {
    // echo "المستخدم admin موجود بالفعل.";
}

// echo "✅ تم إعداد قاعدة البيانات والجداول بنجاح\n";

$conn->close();
?>
