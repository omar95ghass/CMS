<?php
session_start();
// اختبار بسيط للتحقق من الجلسة
echo "Session data:\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "windowNumber: " . ($_SESSION['windowNumber'] ?? 'NOT SET') . "\n";
?>