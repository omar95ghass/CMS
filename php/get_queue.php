<?php
session_start();
header('Content-Type: application/json');

// الاتصال بقاعدة البيانات
include 'db.php';

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    error_log('User not logged in');
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$sql = "SELECT * FROM queue WHERE user_id = ? AND date = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

$queueData = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $queueData[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $queueData]);
} else {
    echo json_encode(['status' => 'success', 'data' => []]);
}

$stmt->close();
$conn->close();
?>