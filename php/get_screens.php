<?php
header('Content-Type: application/json');
require 'db.php';
$res = $conn->query("SELECT * FROM screens ORDER BY screen_number");
$screens = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode(['screens'=>$screens]);
