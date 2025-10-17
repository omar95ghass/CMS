<?php
// index_assign.php
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ربط المستخدمين بالشاشات</title>
  <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4" style="text-align: right;">
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
            <li class="nav-item"><a class="btn btn-sm btn-outline-danger mx-1" href="printers.php">الدور</a></li>
        </ul>
        <a class="btn btn-outline-dark mx-1" href="php/logout_function.php">تسجيل خروج</a>
        </div>
    </div>
    </nav>
  <div class="container">
    <h1 class="mb-4">ربط المستخدمين بالشاشات</h1>
    <table class="table table-bordered">
      <thead><tr><th>المستخدم</th><th>الشاشة المربوطة</th><th>إجراء</th></tr></thead>
      <tbody id="assignTable"></tbody>
    </table>
  </div>

  <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
  <script>
    $(function(){
      const table = $('#assignTable');
      function load() {
        $.get('php/get_users_screens.php', data=>{
          table.empty();
          data.users.forEach(u=>{
            let opts = '<option value="">---</option>';
            data.screens.forEach(s=>{
              opts += `<option value="${s.id}" ${u.assigned_screen==s.id?'selected':''}>شباك ${s.screen_number}</option>`;
            });
            table.append(`
              <tr>
                <td>${u.username}</td>
                <td>
                  <select class="form-control screen-select" data-user="${u.id}">
                    ${opts}
                  </select>
                </td>
                <td><button class="btn btn-sm btn-success save-btn" data-user="${u.id}">حفظ</button></td>
              </tr>
            `);
          });
        },'json');
      }

      table.on('click','.save-btn', function(){
        const user = $(this).data('user');
        const screen = $(`.screen-select[data-user=${user}]`).val();
        $.post('php/assign_screen.php',{user_id:user, screen_id:screen}, res=>{
          if(res.status!=='success') alert(res.message);
        },'json');
      });

      load();
    });
  </script>
</body>
</html>
