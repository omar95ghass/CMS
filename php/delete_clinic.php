<?php
require 'db.php';
$id = intval($_GET['id'] ?? 0);
if ($id>0) {
    $d = $conn->prepare("DELETE FROM services WHERE id=?");
    $d->bind_param('i',$id);
    $d->execute();
}
header('Location: index.php');
