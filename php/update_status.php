<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

include 'db.php';

$id = $_POST['id'];
$status = $_POST['status'];
$today = date('Y-m-d');

$sql = "UPDATE queue SET status = ? WHERE number = ? AND date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sis', $status, $id, $today);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>
