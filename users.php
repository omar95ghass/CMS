<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة المستخدمين</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4" style="text-align: right;">
  <nav class="navbar navbar-expand-lg pb-5">
    <div class="container-fluid">
        <a class="navbar-brand text-light" href="index.php">واجهة الخدمات</a>
        <div class="collapse navbar-collapse d-flex justify-content-between">
        <ul class="navbar-nav me-auto">
            <li class=""><a class="btn btn-sm btn-outline-primary mx-1" href="services.php">إدارة الخدمات</a></li>
            <li class=""><a class="btn btn-sm btn-outline-secondary mx-1" href="display.php">النداء</a></li>
            <li class="nav-item"><a class="btn btn-sm btn-outline-warning mx-1" href="users.php">المستخدمين</a></li>
            <!-- <li class="nav-item"><a class="btn btn-sm btn-outline-danger mx-1" href="printers.php">الدور</a></li> -->
        </ul>
        <a class="btn btn-sm btn-danger mx-1" href="php/logout_function.php">تسجيل خروج</a>
        </div>
    </div>
    </nav>
  <div class="container">
    <div class="row  d-flex justify-content-between align-items-center p-4">
      <h1 class="mb-4">إدارة المستخدمين</h1>
      <button id="btnNew" class="btn btn-primary mb-3">إضافة مستخدم جديد</button>
    </div>
    <table class="table table-bordered" style="text-align: center;">
      <thead>
        <tr>
          <th>#</th>
          <th>اسم المستخدم</th>
          <th>الدور</th>
          <th>شباك</th>
          <th>تاريخ الإنشاء</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody id="usersTable">
        <!-- يُعبأ عبر js -->
      </tbody>
    </table>
  </div>

  <!-- نموذج الإضافة / التعديل -->
  <div class="modal" tabindex="-1" id="userModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="userForm">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">إضافة مستخدم</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="userId">
            <div class="form-group">
              <label>اسم المستخدم</label>
              <input type="text" id="username" class="form-control" required>
            </div>
            <div class="form-group">
              <label>كلمة المرور</label>
              <input type="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
            <label>رقم الشباك</label>
            <select id="window_number" class="form-control">
                <option value="">0</option>
                <!-- توليد الخيارات من 1 إلى 20 -->
                <?php for($i=1; $i<=20; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            </div>
            <div class="form-group">
              <label>الدور</label>
              <select id="role" class="form-control">
                <option value="admin">مشرف</option>
                <option value="counter">شباك</option>
                <option value="cashier">محاسب</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">حفظ</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
  <script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="js/users.js"></script>
</body>
</html>
