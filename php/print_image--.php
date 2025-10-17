<?php
// إيقاف عرض الأخطاء لضمان استجابة JSON نظيفة
ini_set('display_errors', 0);
error_reporting(0); 

header('Content-Type: application/json');

// تأكد من صحة مسار autoload.php (عادةً يكون في مجلد vendor إذا كنت تستخدم Composer)
require '../libs/mike42/escpos-php/autoload.php'; 
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

$payload = json_decode(file_get_contents('php://input'), true);

if (empty($payload['image'])) {
    echo json_encode(['status' => 'error', 'message' => 'No image payload provided']);
    exit;
}

// 1. معالجة وتجهيز الصورة
$base64Image = $payload['image'];
// إزالة الرأس (Data URL header)
$imgData = preg_replace('/^data:image\/(png|jpg|jpeg);base64,/', '', $base64Image);
$bin = base64_decode($imgData);

if ($bin === false || strlen($bin) < 100) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to decode Base64 image data or data is too small.']);
    exit;
}

$temp = null;
$errors = [];
$printSuccess = false;

try {
    // تحديد مجلد الحفظ المؤقت داخل المجلد الحالي (php)
    $tempDir = __DIR__ . '/temp_files';
    
    // إنشاء المجلد إذا لم يكن موجودًا
    if (!is_dir($tempDir)) {
        // نستخدم 0777 للوصول الكامل، و true للسماح بإنشاء المجلدات الأبوية إذا لزم الأمر
        if (!mkdir($tempDir, 0777, true)) {
             throw new Exception("Failed to create temporary directory: " . $tempDir);
        }
    }
    
    // تعريف المسار الكامل للملف المؤقت داخل مجلد المشروع
    $temp = $tempDir . '/queue_ticket_print_' . uniqid() . '.png';
    $bytesWritten = file_put_contents($temp, $bin);

    if ($bytesWritten === false) {
        throw new Exception("Failed to save image to temporary file in project directory.");
    }
    
    // تعريف الطابعة الثابتة
    $printerName = 'MP-POS80'; 
    
    // إنشاء الاتصال والطابعة
    $connector = new WindowsPrintConnector($printerName);
    $printer = new Printer($connector);

    // تحميل الصورة (هنا تحدث مشكلة GD/Imagick)
    $imgObj = EscposImage::load($temp); 
    
    // الطباعة
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->bitImage($imgObj);
    $printer->feed(3);

    $printer->cut();
    $printer->pulse();
    $printer->close();
    
    $printSuccess = true;

} catch (Exception $e) {
    // التقاط أي خطأ (GD/Imagick، الاتصال بالطابعة، إلخ.)
    $errors[] = $e->getMessage();
}

// التنظيف النهائي: يجب أن يتم حذف الملف المؤقت دائمًا
if ($temp !== null && file_exists($temp)) {
    // @unlink($temp); 
}

// 2. إرجاع الاستجابة النهائية
if ($printSuccess) {
    echo json_encode([
        'status' => 'success',
        'message' => 'تم طباعة التذكرة بنجاح.'
    ]);
} else {
    // إذا حدث خطأ في الطباعة، نرجع رسالة الخطأ للمستخدم
    echo json_encode([
        'status' => 'partial_success',
        'message' => 'فشل في الطباعة: ' . implode('; ', $errors)
    ]);
}
?>