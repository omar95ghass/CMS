<?php
require 'db.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT name FROM services WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($name);
if (!$stmt->fetch()) {
    die('خدمة غير موجودة');
}
$stmt->close();

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $newName = trim($_POST['name']);
    if ($newName==='') {
        $error = 'الرجاء إدخال اسم الخدمة';
    } else {
        $u = $conn->prepare("UPDATE services SET name=? WHERE id=?");
        $u->bind_param('si',$newName,$id);
        $u->execute();
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head>
  <meta charset="UTF-8"><title>تعديل خدمة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4">
  <div class="container">
    <h1>تعديل الخدمة</h1>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>اسم الخدمة</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
      </div>
      <button class="btn btn-warning">تعديل</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </form>
  </div>
</body></html>
