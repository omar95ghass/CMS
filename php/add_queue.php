<?php
// تمكين الإبلاغ عن جميع الأخطاء لأغراض التطوير
error_reporting(E_ALL);
ini_set('display_errors', 1); 

header('Content-Type: application/json');

try {
    
  require 'db.php'; 
  
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
  $stmt = $conn->prepare(
    "SELECT MAX(number) AS max_number
    FROM queue
    WHERE `date` = ?"
  );
  $stmt->bind_param('s', $date);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $max_number = $res['max_number'] ?? 0;
  $number   = $max_number + 1;
  $stmt->close();
  
  // إضافة الدور الجديد لكل شباك مرتبط بالخدمة
  foreach ($user_ids as $user_id) {
    $stmt = $conn->prepare(
      "INSERT INTO queue
      (user_id, clinic, number, status, `date`)
      VALUES (?, ?, ?, 'waiting', ?)"
    );
    $stmt->bind_param('isis', $user_id, $clinic, $number, $date);
    if (!$stmt->execute()) {
      throw new Exception('Insert failed for user_id ' . $user_id . ': ' . $stmt->error);
    }
    $stmt->close();
  }

  // 2. إرجاع بيانات الدور بنجاح للواجهة الأمامية
  echo json_encode([
    'status' => 'success',
    'message' => 'تم إضافة الدور بنجاح.',
    'number' => $number,
    'clinic' => $clinic, // مهم لتوليد الصورة
    'user_ids' => $user_ids
  ]);


} catch (PDOException $dbEx) {
  error_log("Database Error: " . $dbEx->getMessage());
  http_response_code(500);
  echo json_encode([
    'status' => 'error',
    'message' => 'خطأ في قاعدة البيانات',
    'error_code' => $dbEx->getCode()
  ]);

} catch (Exception $ex) {
  error_log("General Error: " . $ex->getMessage());
  http_response_code(400);
  echo json_encode([
    'status' => 'error',
    'message' => $ex->getMessage(),
    'error_code' => $ex->getCode()
  ]);

} finally {
  if (isset($conn)) {
    $conn = null;
  }
}
?>