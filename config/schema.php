<?php
// dump_schema.php
// يطبع تعليمات CREATE لكل جدول في قاعدة البيانات

// إعدادات الاتصال
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'queue_db';  // غيّر إلى اسم قاعدتك

// إنشاء الاتصال
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// نطبع الرؤوس
header('Content-Type: text/plain; charset=utf-8');
// echo "-- Schema dump of `$db`\n";
// echo "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

// جلب كل أسماء الجداول
$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

// لكل جدول، ننفذ SHOW CREATE TABLE
foreach ($tables as $table) {
    // echo "--\n-- Structure for table `$table`\n--\n\n";
    $res2 = $conn->query("SHOW CREATE TABLE `{$table}`");
    $row2 = $res2->fetch_assoc();
    // في عمود 'Create Table'
    echo $row2['Create Table'] . ";\n\n";
}

$conn->close();
?>
