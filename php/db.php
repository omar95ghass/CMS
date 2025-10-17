 
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "queue_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // تعيين ترميز UTF-8
    $conn->set_charset("utf8mb4");
    
    // تعيين وضع SQL الآمن
    $conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    
} catch (Exception $e) {
    // توجيه إلى صفحة الأخطاء
    error_log("Database connection error: " . $e->getMessage());
    header("Location: ../error.php?error=db_connection&message=" . urlencode($e->getMessage()));
    exit();
}
?>
