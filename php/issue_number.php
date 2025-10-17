<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clinic = $_POST['clinic'];
    $today = date('Y-m-d');

    // التأكد من أن الرقم يبدأ من 1 في كل يوم جديد
    $result = $conn->query("SELECT MAX(number) as max_number FROM queue WHERE clinic='$clinic' AND date='$today'");
    $row = $result->fetch_assoc();
    $new_number = $row['max_number'] ? $row['max_number'] + 1 : 1;

    $conn->query("INSERT INTO queue (clinic, number, status, date) VALUES ('$clinic', $new_number, 'waiting', '$today')");

    echo json_encode(['status' => 'success', 'number' => $new_number]);
}
$conn->close();
?>
