<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

$result = $conn->query("
    SELECT
        q.clinic,
        q.number,
        qu.window_number
    FROM queue q
    LEFT JOIN queue_users qu ON qu.id = q.user_id
    WHERE q.status = 'called' OR q.status = 'announced'
    ORDER BY q.id ASC
");

$calls = [];
while ($row = $result->fetch_assoc()) {
    $calls[] = $row;
}
if (count($calls) > 0) {
    echo json_encode(['status'=>'success','calls'=>$calls]);
} else {
    echo json_encode(['status'=>'error','message'=>'No numbers to announce']);
}
$conn->close();
?>
