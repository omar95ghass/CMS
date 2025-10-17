<?php
session_start();
header('Content-Type: application/json');

// الاتصال بقاعدة البيانات
include 'db.php';

$today = date('Y-m-d');
$sql = "SELECT
            uc.clinic,
            GROUP_CONCAT(qc.window_number) AS w_number,
            GROUP_CONCAT(uc.user_id) AS user_ids
        FROM
            user_clinics uc
        LEFT JOIN queue_users qc ON qc.id = uc.user_id
        GROUP BY
            clinic";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $clinics = [];
    while($row = $result->fetch_assoc()) {
        $row['user_ids'] = explode(',', $row['user_ids']);
        $row['w_number'] = explode(',', $row['w_number']);
        $clinics[] = $row;
    }
    echo json_encode(['status' => 'success', 'clinics' => $clinics]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No active clinics found']);
}

$conn->close();
?>
