<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'),true);
$stmt = $conn->prepare(
  "UPDATE screens SET screen_number=?, ip=?, port=? WHERE id=?"
);
$stmt->bind_param('isii',
  $data['screen_number'],$data['ip'],$data['port'],$data['id']
);
if($stmt->execute()) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>$stmt->error]);
