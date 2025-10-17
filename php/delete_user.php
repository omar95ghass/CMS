<?php
header('Content-Type: application/json');
require 'db.php';

$id = intval($_POST['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM queue_users WHERE id=?");
$stmt->bind_param('i',$id);
if ($stmt->execute()) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>'خطأ في الحذف']);
