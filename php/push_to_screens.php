<?php
header('Content-Type: application/json');
require 'db.php';

$result = $conn->query("
    SELECT 
      s.id, s.ip, s.port, s.screen_number,
      q.number
    FROM screens s
    LEFT JOIN (
      SELECT user_id, number 
      FROM queue 
      WHERE status = 'called' 
      ORDER BY id DESC
    ) q ON q.user_id = (
      SELECT user_id FROM queue_users WHERE assigned_screen = s.id LIMIT 1
    )
");

$screens = [];
while ($row = $result->fetch_assoc()) {
    $screens[] = $row;
}

$statuses = [];
foreach ($screens as $s) {
    $num = intval($s['number']);

    // تنسيق الرقم حسب المطلوب
    if ($num < 10) {
        $p = '00' . $num;
    } elseif ($num < 100) {
        $p = '0' . $num;
    } else {
        $p = (string)$num;
    }

    if (empty($s['ip']) || empty($s['port']) || $p === '') {
        $statuses[] = [
            'screen' => $s['screen_number'],
            'status' => 'skipped',
            'reason' => 'no number or no ip/port'
        ];
        continue;
    }

    // بناء URL مع التنسيق الجديد
    $url = sprintf(
        'http://%s:%s/display?cmd=d&p=%s',
        $s['ip'],
        $s['port'],
        $p
    );

    // نفذ الطلب
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    $statuses[] = [
        'screen'   => $s['screen_number'],
        'url'      => $url,
        'success'  => $err === '',
        'response' => $resp ?: null,
        'error'    => $err ?: null
    ];
}

echo json_encode(['status' => 'done', 'results' => $statuses]);
$conn->close();
?>
