<?php
require 'php/db.php';
$result = $conn->query("SELECT * FROM services ORDER BY id");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الخدمات</title>
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="text-align: right;" class="p-4">
    <nav class="navbar navbar-expand-lg pb-5">
    <div class="container-fluid">
        <a class="navbar-brand text-light" href="index.php">واجهة الخدمات</a>
        <div class="collapse navbar-collapse d-flex justify-content-between">
        <ul class="navbar-nav me-auto">
            <li class=""><a class="btn btn-sm btn-outline-primary mx-1" href="services.php">إدارة الخدمات</a></li>
            <li class=""><a class="btn btn-sm btn-outline-primary mx-1" href="index_screens.php">إدارة شاشات النوافذ</a></li>
            <li class=""><a class="btn btn-sm btn-outline-primary mx-1" href="index_assign.php">تعيين شاشات النوافذ</a></li>
            <li class=""><a class="btn btn-sm btn-outline-secondary mx-1" href="display.php">النداء</a></li>
            <li class="nav-item"><a class="btn btn-sm btn-outline-warning mx-1" href="users.php">المستخدمين</a></li>
            <!-- <li class="nav-item"><a class="btn btn-sm btn-outline-danger mx-1" href="printers.php">الدور</a></li> -->
        </ul>
        <a class="btn btn-outline-dark mx-1" href="php/logout_function.php">تسجيل خروج</a>
        </div>
    </div>
    </nav>
  <div class="container">
    <div class="row d-flex justify-content-between align-items-center p-4">
        <h1 class="mb-4">إدارة الخدمات</h1>
        <a href="php/create_clinic.php" class="btn btn-primary mb-3">إضافة خدمة جديدة</a>
    </div>
    <table class="table table-striped" style="text-align: center;">
      <thead><tr><th>#</th><th>اسم الخدمة</th><th>إجراءات</th></tr></thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <a href="php/edit_clinic.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
            <a href="php/delete_clinic.php?id=<?= $row['id'] ?>" 
               class="btn btn-sm btn-danger"
               onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟');">
              حذف
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
