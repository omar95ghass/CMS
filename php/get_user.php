<?php
header('Content-Type: application/json');
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, username, role, window_number  FROM queue_users WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if ($res) echo json_encode(['status'=>'success','user'=>$res]);
else       echo json_encode(['status'=>'error','message'=>'غير موجود']);
