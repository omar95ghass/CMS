<?php
header('Content-Type: application/json');
require 'db.php';
$id = intval($_POST['id']);
$stmt = $conn->prepare("DELETE FROM screens WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
echo json_encode(['status'=>'success']);
