<?php
header('Content-Type: application/json');
require 'db.php';

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['status'=>'error','message'=>'Invalid id']);
    exit;
}

$stmt = $conn->prepare("UPDATE queue SET status='announced' WHERE id=?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
