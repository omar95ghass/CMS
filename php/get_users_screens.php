<?php
header('Content-Type: application/json');
require 'db.php';
$users = $conn->query(
  "SELECT id, username, assigned_screen FROM queue_users ORDER BY id"
)->fetch_all(MYSQLI_ASSOC);
$screens = $conn->query(
  "SELECT id, screen_number FROM screens ORDER BY screen_number"
)->fetch_all(MYSQLI_ASSOC);
echo json_encode(['users'=>$users,'screens'=>$screens]);
