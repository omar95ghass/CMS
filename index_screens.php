<?php

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الشاشات</title>
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4" style="text-align: right;">
    <nav class="navbar navbar-expand-lg pb-5">
    <div class="container-fluid">
        <a class="navbar-brand text-light" href="index.php">واجهة الخدمات</a>
        <div class="collapse navbar-collapse d-flex justify-content-between">
        <ul class="navbar-nav me-auto">
            <li class=""><a class="btn btn-sm btn-outline-primary mx-1" href="services.php">إدارة الخدمات</a></li>
            <li class=""><a class="btn btn-sm btn-outline-info mx-1" href="index_screens.php">إدارة شاشات النوافذ</a></li>
            <li class=""><a class="btn btn-sm btn-outline-success mx-1" href="index_assign.php">تعيين شاشات النوافذ</a></li>
            <li class=""><a class="btn btn-sm btn-outline-secondary mx-1" href="display.php">النداء</a></li>
            <li class="nav-item"><a class="btn btn-sm btn-outline-warning mx-1" href="users.php">المستخدمين</a></li>
            <li class="nav-item"><a class="btn btn-sm btn-outline-danger mx-1" href="printers.php">الدور</a></li>
        </ul>
        <a class="btn btn-outline-dark mx-1" href="php/logout_function.php">تسجيل خروج</a>
        </div>
    </div>
    </nav>
  <div class="container">
    <h1 class="mb-4">إدارة الشاشات</h1>
    <button id="btnNewScreen" class="btn btn-primary mb-3">إضافة شاشة جديدة</button>
    <table class="table table-bordered">
      <thead>
        <tr><th>#</th><th>رقم الشاشة</th><th>IP</th><th>Port</th><th>إجراءات</th></tr>
      </thead>
      <tbody id="screensTable"></tbody>
    </table>
  </div>

  <!-- مودال الإضافة/التعديل -->
  <div class="modal" id="screenModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="screenForm">
          <div class="modal-header">
            <h5 class="modal-title" id="screenModalTitle">إضافة شاشة</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="screenId">
            <div class="form-group">
              <label>رقم الشاشة</label>
              <input type="number" id="screenNumber" class="form-control" required min="1">
            </div>
            <div class="form-group">
              <label>IP</label>
              <input type="text" id="screenIp" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Port</label>
              <input type="number" id="screenPort" class="form-control" required min="1" max="65535">
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success">حفظ</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
  <script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="js/screens.js"></script>
</body>
</html>
