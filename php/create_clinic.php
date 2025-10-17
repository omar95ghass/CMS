<?php
require 'db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name']);
    if ($name==='') {
        $error = 'الرجاء إدخال اسم الخدمة';
    } else {
        $stmt = $conn->prepare("INSERT INTO services (name) VALUES (?)");
        $stmt->bind_param('s',$name);
        $stmt->execute();
        header('Location: ../services.php');
        exit;
    }
}
?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head>
  <meta charset="UTF-8"><title>إضافة خدمة</title>
  <link href="../css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4" style="text-align: right;">
  <div class="container">
    <h1>إضافة خدمة جديدة</h1>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>اسم الخدمة</label>
        <input name="name" class="form-control" required>
      </div>
      <button class="btn btn-success">حفظ</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </form>
  </div>
</body></html>
