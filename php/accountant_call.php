<?php
include 'db.php';

$number = $_POST['number'];
// $clinic = $_POST['clinic'];
$today = date('Y-m-d');

$result = $conn->query("SELECT * FROM queue WHERE number=$number AND date='$today'");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $conn->query("UPDATE queue SET status='called' WHERE id=" . $row['id']);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Number not found']);
}

$conn->close();
?>
