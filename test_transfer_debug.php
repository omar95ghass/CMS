<?php
// اختبار بسيط لـ transfer_queue.php
session_start();

// محاكاة جلسة المستخدم
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'counter';

// محاكاة البيانات
$testData = [
    'number' => 1,
    'clinic' => 'قيد',
    'target_user_id' => 7
];

// إرسال البيانات
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/transfer_queue.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

// محاولة تحليل JSON
$decoded = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "JSON is valid\n";
    print_r($decoded);
} else {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    echo "Raw response: " . htmlspecialchars($response) . "\n";
}
?>