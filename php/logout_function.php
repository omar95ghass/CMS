<?php
session_start();

// تسجيل عملية تسجيل الخروج
if (isset($_SESSION['username'])) {
    error_log("User " . $_SESSION['username'] . " logged out at " . date('Y-m-d H:i:s'));
}

// إزالة جميع متغيرات الجلسة
$_SESSION = array();

// حذف ملف الجلسة
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header("Location: ../login.php");
exit();
?>
