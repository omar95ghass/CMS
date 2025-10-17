<?php
include 'db.php';

$result = $conn->query("SELECT * FROM services");
$clinics = [];

while ($row = $result->fetch_assoc()) {
    $clinics[] = $row;
}

echo json_encode($clinics);

$conn->close();
?>
