<?php
// ملف اختبار لإعادة النداء
session_start();

// محاكاة تسجيل الدخول كـ counter
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'counter';

echo "<h2>اختبار إعادة النداء</h2>";

// اختبار recall_queue.php
echo "<h3>اختبار recall_queue.php:</h3>";

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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: $response</p>";

// اختبار get_pending_calls.php
echo "<h3>اختبار get_pending_calls.php:</h3>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/get_pending_calls.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: $response</p>";

// اختبار قاعدة البيانات مباشرة
echo "<h3>اختبار قاعدة البيانات:</h3>";

try {
    include 'php/db.php';
    
    $stmt = $conn->prepare("
        SELECT q.*, u.window_number 
        FROM queue q 
        JOIN queue_users u ON q.user_id = u.id 
        WHERE q.number = ? AND q.clinic = ? AND q.date = CURDATE() 
        AND (q.status = 'called' OR q.status = 'announced')
        ORDER BY q.created_at DESC
        LIMIT 5
    ");
    
    $stmt->bind_param('is', 1, 'قيد');
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