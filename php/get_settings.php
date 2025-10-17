<?php
header('Content-Type: application/json');

try {
    include 'db.php';
    
    $settings = [];
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $stmt->close();
    $conn->close();
    
    // إعدادات افتراضية إذا لم تكن موجودة
    $defaultSettings = [
        'center_name' => 'مركز خدمة المواطن',
        'ticker_messages' => "✅ مركز خدمة المواطن في دمر\n⚠️ يرجى الاحتفاظ بتذكرة الدور\n📢 لا تخرج من الصالة لتتمكن من سماع النداء\n💡 نتمنى لكم يوماً طيباً",
        'printer_name' => 'EPSON TM-T20'
    ];
    
    $settings = array_merge($defaultSettings, $settings);
    
    echo json_encode([
        'status' => 'success',
        'settings' => $settings
    ]);
    
} catch (Exception $e) {
    error_log("Get settings error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'settings' => [
            'center_name' => 'مركز خدمة المواطن',
            'ticker_messages' => "✅ مركز خدمة المواطن في دمر\n⚠️ يرجى الاحتفاظ بتذكرة الدور\n📢 لا تخرج من الصالة لتتمكن من سماع النداء\n💡 نتمنى لكم يوماً طيباً",
            'printer_name' => 'EPSON TM-T20'
        ]
    ]);
}
?>