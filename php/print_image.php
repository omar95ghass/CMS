<?php
// إظهار الأخطاء للمساعدة في التطوير، يمكن إزالتها في مرحلة الإنتاج
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
header('Access-Control-Allow-Headers:Content-Type');

// تهيئة المتغيرات الأساسية خارج try
$temp = null; 
$errors = []; // تهيئة مصفوفة الأخطاء
$success = false;

$payload = json_decode(file_get_contents('php://input'), true);
$base64Image = $payload['image'] ?? null;

if (empty($base64Image)) {
    echo json_encode(['success' => false, 'error' => 'No image payload provided']);
    exit;
}

// فك تشفير الصورة
$imgData = preg_replace('/^data:image\/(png|jpg|jpeg);base64,/', '', $base64Image);
$bin = base64_decode($imgData);

if ($bin === false || strlen($bin) < 100) {
    echo json_encode(['success' => false, 'error' => 'Failed to decode Base64 image data.']);
    exit;
}

// متطلب المكتبة
require __DIR__.'/../vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

try {
    // 1. إنشاء وحفظ الملف المؤقت داخل المشروع
    $tempDir = __DIR__ . '/temp_files';
    if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true)) {
         throw new Exception("Failed to create temporary directory.");
    }
    
    $temp = $tempDir . '/queue_ticket_print_' . uniqid() . '.png';
    
    if (file_put_contents($temp, $bin) === false) {
        throw new Exception("Failed to save image to temporary file.");
    }

    // 2. جلب إعدادات الطابعة
    $printerName = 'MP-80'; // الافتراضي
    try {
        $settingsStmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'printer_name'");
        $settingsStmt->execute();
        $result = $settingsStmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $printerName = $row['setting_value'];
        }
        $settingsStmt->close();
    } catch (Exception $e) {
        error_log("Error fetching printer settings: " . $e->getMessage());
    }
    
    // 3. الطباعة
    $conn = new WindowsPrintConnector($printerName);
    $printer = new Printer($conn);
    
    // تحميل الصورة (يتطلب تفعيل مكتبة GD في php.ini)
    $imgObj = EscposImage::load($temp); 
    
    $printer->bitImage($imgObj);
    $printer->pulse();
    $printer->cut();
    $printer->close();
    
    $success = true;

} catch (Exception $e) {
    // التقاط الخطأ وإضافته للمصفوفة
    $errors[] = $e->getMessage();
}

// 3. التنظيف: حذف الملف المؤقت في جميع الأحوال
if ($temp !== null && file_exists($temp)) {
    //@unlink($temp);
}

echo json_encode([
    'success' => $success,
    'error'   => $success ? '' : implode('; ', $errors)
]);