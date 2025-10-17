<?php
session_start();
header('Content-Type: application/json');
include 'db.php';


$user = $_SESSION['user_id'];
// echo $user;
$today = date('Y-m-d');
// echo '<br>' . $today;

// البحث عن الدور التالي للحالة waiting لهذا الشباك
$stmt = $conn->prepare("SELECT id, number FROM queue WHERE user_id = ? AND status = 'waiting' AND date = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('is', $user, $today);
$stmt->execute();
$result = $stmt->get_result();

// echo $result->num_rows;
if ($result->num_rows > 0) {
    // echo 'hey';
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $number = $row['number'];

    // تحديث حالة الدور إلى 'called' - فقط للدور المحدد
    $stmt_update = $conn->prepare("UPDATE queue SET status = 'called' WHERE id = ?");
    $stmt_update->bind_param('i', $id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // تسجيل النداء في السجل
    error_log("Queue called: Number $number (ID: $id) by user $user");

    echo json_encode(['status' => 'success', 'number' => $number, 'id' => $id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No waiting numbers found']);
}

$stmt->close();
$conn->close();
?>