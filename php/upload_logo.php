<?php
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// التحقق من صلاحيات الإدارة
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة']);
    exit();
}

try {
    // التحقق من وجود الملف
    if (!isset($_FILES['logo_upload']) || $_FILES['logo_upload']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'لم يتم رفع الملف أو حدث خطأ في الرفع']);
        exit();
    }
    
    $file = $_FILES['logo_upload'];
    
    // التحقق من نوع الملف
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'نوع الملف غير مدعوم. يرجى اختيار صورة (JPG, PNG, GIF, WebP)']);
        exit();
    }
    
    // التحقق من حجم الملف (2MB)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        echo json_encode(['status' => 'error', 'message' => 'حجم الملف كبير جداً. الحد الأقصى 2MB']);
        exit();
    }
    
    // إنشاء مجلد اللوغو إذا لم يكن موجوداً
    $logo_dir = '../images/logo/';
    if (!is_dir($logo_dir)) {
        mkdir($logo_dir, 0755, true);
    }
    
    // إنشاء نسخة احتياطية من اللوغو الحالي
    $current_logo = $logo_dir . 'logo.png';
    if (file_exists($current_logo)) {
        $backup_logo = $logo_dir . 'logo_backup_' . date('Y-m-d_H-i-s') . '.png';
        copy($current_logo, $backup_logo);
    }
    
    // تحديد اسم الملف الجديد
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'logo.' . $file_extension;
    $target_path = $logo_dir . $new_filename;
    
    // نقل الملف إلى المجلد المحدد
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // إنشاء نسخة PNG للتوافق
        $png_path = $logo_dir . 'logo.png';
        
        // تحويل الصورة إلى PNG إذا لم تكن كذلك
        if ($file_extension !== 'png') {
            $image = null;
            
            switch ($file['type']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($target_path);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($target_path);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($target_path);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($target_path);
                    break;
            }
            
            if ($image) {
                // تغيير حجم الصورة إذا كانت كبيرة جداً
                $max_width = 300;
                $max_height = 200;
                
                $original_width = imagesx($image);
                $original_height = imagesy($image);
                
                if ($original_width > $max_width || $original_height > $max_height) {
                    $ratio = min($max_width / $original_width, $max_height / $original_height);
                    $new_width = $original_width * $ratio;
                    $new_height = $original_height * $ratio;
                    
                    $resized_image = imagecreatetruecolor($new_width, $new_height);
                    imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
                    
                    imagedestroy($image);
                    $image = $resized_image;
                }
                
                // حفظ كـ PNG
                imagepng($image, $png_path, 9);
                imagedestroy($image);
                
                // حذف الملف الأصلي إذا لم يكن PNG
                if ($file_extension !== 'png') {
                    unlink($target_path);
                }
            }
        }
        
        // حفظ مسار اللوغو في قاعدة البيانات
        include 'dual_db.php';
        
        $logo_path = 'images/logo/logo.png';
        $dual_db->update('system_settings', 
            ['setting_value' => $logo_path], 
            'setting_key = ?', 
            ['logo_path']
        );
        
        // إدراج إعداد جديد إذا لم يكن موجوداً
        if ($dual_db->query("SELECT COUNT(*) as count FROM system_settings WHERE setting_key = 'logo_path'")->fetch()['count'] == 0) {
            $dual_db->insert('system_settings', [
                'setting_key' => 'logo_path',
                'setting_value' => $logo_path
            ]);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'تم حفظ اللوغو بنجاح',
            'logo_path' => $logo_path
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل في حفظ الملف']);
    }
    
} catch (Exception $e) {
    error_log("Logo upload error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في رفع اللوغو: ' . $e->getMessage()]);
}
?>