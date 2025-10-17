<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // 1) اجلب الـ raw input
    $raw = file_get_contents('php://input');
    // 2) حاول فكّ الـ JSON
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('خطأ في JSON: ' . json_last_error_msg() . '. raw_input: ' . $raw);
    }

    $username      = trim($data['username'] ?? '');
    $password      = $data['password'] ?? '';
    $role          = $data['role'] ?? '';
    $window_number = intval($data['window_number'] ?? 0);

    // 3) تحقق من المدخلات
    if ($username === '' || $password === '' || $role === '') {
        throw new Exception('جميع الحقول (username, password, role) مطلوبة. البيانات المستلمة: ' . $raw);
    }

    // 4) هيّئ الاستعلام
    $hash = $password;
    $stmt = $conn->prepare("
        INSERT INTO queue_users (username, password, role, window_number)
        VALUES (?, ?, ?, ?)
    ");
    if (!$stmt) {
        throw new Exception('خطأ في prepare: ' . $conn->error);
    }
    $stmt->bind_param('sssi', $username, $hash, $role, $window_number);

    // 5) نفّذ
    if (!$stmt->execute()) {
        throw new Exception('خطأ في execute: ' . $stmt->error);
    }

    // 6) نجح
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    // لا نغيّر كود الحالة هنا لكي يظهر الرد في الكونسول
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
