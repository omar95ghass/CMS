<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('خطأ في JSON: ' . json_last_error_msg() . '. raw_input: ' . $raw);
    }

    $id            = intval($data['id'] ?? 0);
    $username      = trim($data['username'] ?? '');
    $role          = $data['role'] ?? '';
    $window_number = intval($data['window_number'] ?? 0);

    if ($id <= 0) {
        throw new Exception('id غير صالح. البيانات المستلمة: ' . $raw);
    }
    if ($username === '' || $role === '') {
        throw new Exception('username و role مطلوبان. البيانات: ' . $raw);
    }
    if ($window_number < 1 || $window_number > 20) {
        throw new Exception('window_number خارج النطاق [1-20]. القيمة: ' . $raw);
    }

    if (!empty($data['password'])) {
        $hash = $data['password'];
        $sql  = "UPDATE queue_users SET username=?, password=?, role=?, window_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('خطأ في prepare: ' . $conn->error);
        }
        $stmt->bind_param('sssii', $username, $hash, $role, $window_number, $id);
    } else {
        $sql  = "UPDATE queue_users SET username=?, role=?, window_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('خطأ في prepare: ' . $conn->error);
        }
        $stmt->bind_param('siii', $username, $role, $window_number, $id);
    }

    if (!$stmt->execute()) {
        throw new Exception('خطأ في execute: ' . $stmt->error);
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
