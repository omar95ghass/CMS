<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'),true);
$stmt = $conn->prepare(
  "INSERT INTO screens (screen_number, ip, port) VALUES (?,?,?)"
);
$stmt->bind_param('isi',$data['screen_number'],$data['ip'],$data['port']);
if($stmt->execute()) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>$stmt->error]);
