<?php
header('Content-Type: application/json');
require 'db.php';

$res = $conn->query("
  SELECT 
    q.id, 
    q.number, 
    (SELECT window_number FROM queue_users WHERE id = q.user_id) AS window_number
  FROM queue q
  WHERE q.status = 'called'
  ORDER BY q.id ASC
");

$calls = [];
while ($row = $res->fetch_assoc()) {
    $calls[] = $row;
}

echo json_encode([
    'status' => 'success',
    'calls'  => $calls
]);

$conn->close();
