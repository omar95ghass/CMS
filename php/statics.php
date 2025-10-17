<?php
session_start();
header('Content-Type: application/json');

try {
    include 'db.php';
    
    $today = date('Y-m-d');
    
    // إحصائيات الأدوار النشطة
    $stmt = $conn->prepare("SELECT COUNT(*) as active_queues FROM queue WHERE date = ? AND status IN ('waiting', 'called')");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_queues = $result->fetch_assoc()['active_queues'];
    $stmt->close();
    
    // إحصائيات الأدوار المكتملة اليوم
    $stmt = $conn->prepare("SELECT COUNT(*) as completed_today FROM queue WHERE date = ? AND status = 'completed'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_today = $result->fetch_assoc()['completed_today'];
    $stmt->close();
    
    // إحصائيات الأدوار حسب الخدمة
    $stmt = $conn->prepare("SELECT clinic, COUNT(*) as count FROM queue WHERE date = ? GROUP BY clinic ORDER BY count DESC");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $clinic_stats = [];
    while ($row = $result->fetch_assoc()) {
        $clinic_stats[] = $row;
    }
    $stmt->close();
    
    // إحصائيات الأدوار حسب الحالة
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM queue WHERE date = ? GROUP BY status");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $status_stats = [];
    while ($row = $result->fetch_assoc()) {
        $status_stats[] = $row;
    }
    $stmt->close();
    
    // إحصائيات الأسبوع
    $week_start = date('Y-m-d', strtotime('-7 days'));
    $stmt = $conn->prepare("SELECT DATE(date) as date, COUNT(*) as count FROM queue WHERE date >= ? GROUP BY DATE(date) ORDER BY date");
    $stmt->bind_param("s", $week_start);
    $stmt->execute();
    $result = $stmt->get_result();
    $week_stats = [];
    while ($row = $result->fetch_assoc()) {
        $week_stats[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'active_queues' => $active_queues,
        'completed_today' => $completed_today,
        'clinic_stats' => $clinic_stats,
        'status_stats' => $status_stats,
        'week_stats' => $week_stats,
        'date' => $today
    ]);
    
} catch (Exception $e) {
    error_log("Statistics error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'active_queues' => 0,
        'completed_today' => 0,
        'clinic_stats' => [],
        'status_stats' => [],
        'week_stats' => []
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
