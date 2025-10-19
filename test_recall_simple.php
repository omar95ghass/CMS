<?php
// اختبار بسيط لإعادة النداء
session_start();

// محاكاة تسجيل الدخول
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'counter';

echo "<h2>اختبار إعادة النداء</h2>";

// 1. إنشاء دور للاختبار
echo "<h3>1. إنشاء دور للاختبار:</h3>";
try {
    include 'php/db.php';
    
    // حذف الأدوار القديمة
    $stmt = $conn->prepare("DELETE FROM queue WHERE user_id = ? AND date = CURDATE()");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    
    // إنشاء دور جديد
    $stmt = $conn->prepare("INSERT INTO queue (user_id, clinic, number, status, date) VALUES (?, ?, ?, ?, CURDATE())");
    $status = 'waiting';
    $stmt->bind_param('isi', $_SESSION['user_id'], 'قيد', 1, $status);
    $stmt->execute();
    $queueId = $conn->insert_id;
    $stmt->close();
    
    echo "<p>تم إنشاء دور جديد برقم 1، ID: $queueId</p>";
    
} catch (Exception $e) {
    echo "<p>خطأ في إنشاء الدور: " . $e->getMessage() . "</p>";
}

// 2. نداء الدور
echo "<h3>2. نداء الدور:</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/call_next.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// 3. إعادة النداء
echo "<h3>3. إعادة النداء:</h3>";
$testData = [
    'number' => 1,
    'clinic' => 'قيد'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/recall_queue.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// 4. فحص get_pending_calls
echo "<h3>4. فحص get_pending_calls:</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/get_pending_calls.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// 5. فحص قاعدة البيانات
echo "<h3>5. فحص قاعدة البيانات:</h3>";
try {
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.date = CURDATE() 
        ORDER BY q.created_at DESC
    ");
    
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Number</th><th>Clinic</th><th>Status</th><th>Created At</th><th>Window</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['number'] . "</td>";
        echo "<td>" . $row['clinic'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['window_number'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>خطأ في قاعدة البيانات: " . $e->getMessage() . "</p>";
}
?>