// users.js
$(document).ready(function() {
  const modal = $('#userModal');
  const form = $('#userForm');
  const table = $('#usersTable');

  function loadUsers() {
    $.get('php/get_users.php', data => {
      if (data.status === 'success') {
        table.empty();
        data.users.forEach(u => {
        table.append(`
            <tr>
            <td>${u.id}</td>
            <td>${u.username}</td>
            <td>${u.role}</td>
            <td>${u.window_number || ''}</td>
            <td>${u.created_at}</td>
            <td>
                <button class="btn btn-sm btn-warning edit-btn" data-id="${u.id}">تعديل</button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${u.id}">حذف</button>
            </td>
            </tr>
        `);
        });
      }
    }, 'json');
  }

  $('#btnNew').click(() => {
    $('#modalTitle').text('إضافة مستخدم');
    form.trigger('reset');
    $('#userId').val('');
    modal.modal('show');
  });

  table.on('click', '.edit-btn', function() {
    const id = $(this).data('id');
    $.get(`php/get_user.php?id=${id}`, data => {
      if (data.status === 'success') {
        $('#modalTitle').text('تعديل مستخدم');
        $('#userId').val(data.user.id);
        $('#username').val(data.user.username);
        $('#password').val('');
        $('#window_number').val(data.user.window_number);
        $('#role').val(data.user.role);
        modal.modal('show');
      }
    }, 'json');
  });

  table.on('click', '.delete-btn', function() {
    if (!confirm('هل تريد حذف هذا المستخدم؟')) return;
    const id = $(this).data('id');
    $.post('php/delete_user.php', { id }, data => {
      if (data.status === 'success') loadUsers();
      else alert('فشل في الحذف');
    }, 'json');
  });

  form.submit(function(e) {
    e.preventDefault();
    const payload = {
        id: $('#userId').val(),
        username: $('#username').val(),
        password: $('#password').val(),
        role: $('#role').val(),
        window_number: $('#window_number').val()
    };

    const url = payload.id ? 'php/update_user.php' : 'php/create_user.php';
    console.log(payload);
    $.ajax({
      url,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      success(data) {
        if (data.status === 'success') {
          modal.modal('hide');
          loadUsers();
        } else {
          alert(data.message);
        }
      }
    });
  });

  loadUsers();
});
