<?php
header('Content-Type: application/json');
require 'db.php';
$user_id   = intval($_POST['user_id']);
$screen_id = filter_var($_POST['screen_id'], FILTER_VALIDATE_INT) ?: null;
$stmt = $conn->prepare(
  "UPDATE queue_users SET assigned_screen = ? WHERE id = ?"
);
$stmt->bind_param('ii', $screen_id, $user_id);
if ($stmt->execute()) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>$stmt->error]);
