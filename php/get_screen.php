<?php
header('Content-Type: application/json');
require 'db.php';
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM screens WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$screen = $stmt->get_result()->fetch_assoc();
echo json_encode(['screen'=>$screen]);
