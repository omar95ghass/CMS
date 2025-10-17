<?php
header('Content-Type: application/json');
require 'db.php';

$res = $conn->query("SELECT id, username, role, created_at, window_number FROM queue_users ORDER BY id");
$users = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode(['status'=>'success','users'=>$users]);
