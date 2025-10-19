<?php
// ملف اختبار لإعادة النداء
session_start();

// محاكاة تسجيل الدخول كـ counter
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'counter';

echo "<h2>اختبار إعادة النداء - Debug</h2>";

// 1. اختبار قاعدة البيانات مباشرة
echo "<h3>1. فحص قاعدة البيانات:</h3>";
try {
    include 'php/db.php';
    
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.date = CURDATE() 
        ORDER BY q.created_at DESC
        LIMIT 10
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
    
} catch (Exception $e) {
    echo "<p>خطأ في قاعدة البيانات: " . $e->getMessage() . "</p>";
}

// 2. اختبار get_pending_calls.php
echo "<h3>2. اختبار get_pending_calls.php:</h3>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/get_pending_calls.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// 3. اختبار recall_queue.php
echo "<h3>3. اختبار recall_queue.php:</h3>";

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

// 4. اختبار get_pending_calls.php مرة أخرى بعد إعادة النداء
echo "<h3>4. اختبار get_pending_calls.php بعد إعادة النداء:</h3>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/get_pending_calls.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// 5. فحص قاعدة البيانات مرة أخرى
echo "<h3>5. فحص قاعدة البيانات بعد إعادة النداء:</h3>";
try {
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.user_id = ? AND q.date = CURDATE() 
        ORDER BY q.created_at DESC
        LIMIT 10
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