<?php
// تمكين الإبلاغ عن جميع الأخطاء لأغراض التطوير
error_reporting(E_ALL);
ini_set('display_errors', 1); 

header('Content-Type: application/json');

try {
    
  require 'dual_db.php'; 
  
  // جلب البيانات من الطلب
  $input = file_get_contents('php://input');
  if (empty($input)) {
    throw new Exception("Empty request data");
  }
  
  $data = json_decode($input, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Invalid JSON format: " . json_last_error_msg());
  }

  // التحقق من البيانات الأساسية
  if (empty($data['clinic'])) {
    throw new Exception("Missing required fields: clinic");
  }
  if (empty($data['user_ids']) || !is_array($data['user_ids'])) {
    throw new Exception("Missing required fields: user_ids array");
  }

  $clinic = htmlspecialchars($data['clinic']);
  $user_ids = $data['user_ids']; // مصفوفة من أرقام الشبابيك
  $date = date('Y-m-d');

  // 1. الحصول على آخر رقم الدور وإضافة دور جديد
  $sql = "SELECT MAX(number) AS max_number FROM queue WHERE `date` = ?";
  $result = $dual_db->query($sql, [$date]);
  
  $row = null;
  if ($current_db_type === 'mysql') {
    $row = $result->fetch_assoc();
  } else {
    $row = $result->fetch(PDO::FETCH_ASSOC);
  }
  
  $max_number = $row['max_number'] ?? 0;
  $number = $max_number + 1;
  
  // إضافة الدور الجديد لكل شباك مرتبط بالخدمة
  foreach ($user_ids as $user_id) {
    $queue_data = [
      'user_id' => $user_id,
      'clinic' => $clinic,
      'number' => $number,
      'status' => 'waiting',
      'date' => $date
    ];
    
    $dual_db->insert('queue', $queue_data);
  }

  // 2. إرجاع بيانات الدور بنجاح للواجهة الأمامية
  echo json_encode([
    'status' => 'success',
    'message' => 'تم إضافة الدور بنجاح.',
    'number' => $number,
    'clinic' => $clinic, // مهم لتوليد الصورة
    'user_ids' => $user_ids,
    'database' => $current_db_type
  ]);


} catch (Exception $ex) {
  error_log("General Error: " . $ex->getMessage());
  http_response_code(400);
  echo json_encode([
    'status' => 'error',
    'message' => $ex->getMessage(),
    'error_code' => $ex->getCode()
  ]);
}
?>