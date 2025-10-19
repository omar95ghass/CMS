<?php
session_start();

// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: error.php?error=permission&message=غير مصرح لك بتنفيذ هذا الإجراء");
    exit();
}

try {
    include 'db.php';

    // التحقق من الاتصال بقاعدة البيانات
    if (!$conn) {
        header("Location: error.php?error=db_connection&message=فشل الاتصال بقاعدة البيانات");
        exit();
    }

    // تحديث جميع الحالات إلى completed وتسجيل وقت الإنهاء
    $sql = "UPDATE queue SET status = 'completed', updated_at = NOW()";
    $result = $conn->query($sql);

    if ($result === false) {
        // فشل تنفيذ الاستعلام
        $error_message = urlencode($conn->error);
        header("Location: error.php?error=db_query&message=خطأ أثناء تنفيذ الاستعلام: $error_message");
        exit();
    }

    // في حال النجاح
    header("Location: ../admin_dashboard.php");
    exit();

} catch (Exception $e) {
    $msg = urlencode($e->getMessage());
    header("Location: error.php?error=unknown&message=حدث خطأ غير متوقع: $msg");
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
