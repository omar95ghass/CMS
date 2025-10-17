// screens.js
$(function(){
  const modal = $('#screenModal');
  const form = $('#screenForm');
  const table = $('#screensTable');

  function loadScreens(){
    $.get('php/get_screens.php', data=>{
      table.empty();
      data.screens.forEach(s=>{
        table.append(`
          <tr>
            <td>${s.id}</td>
            <td>${s.screen_number}</td>
            <td>${s.ip}</td>
            <td>${s.port}</td>
            <td>
              <button class="btn btn-sm btn-warning edit-btn" data-id="${s.id}">تعديل</button>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${s.id}">حذف</button>
            </td>
          </tr>
        `);
      });
    },'json');
  }

  $('#btnNewScreen').click(()=>{
    $('#screenModalTitle').text('إضافة شاشة جديدة');
    form.trigger('reset');
    $('#screenId').val('');
    modal.modal('show');
  });

  table.on('click','.edit-btn', function(){
    const id = $(this).data('id');
    $.get(`php/get_screen.php?id=${id}`, data=>{
      $('#screenModalTitle').text('تعديل شاشة');
      $('#screenId').val(data.screen.id);
      $('#screenNumber').val(data.screen.screen_number);
      $('#screenIp').val(data.screen.ip);
      $('#screenPort').val(data.screen.port);
      modal.modal('show');
    },'json');
  });

  table.on('click','.delete-btn', function(){
    if(!confirm('هل تريد حذف هذه الشاشة؟')) return;
    $.post('php/delete_screen.php',{id:$(this).data('id')}, loadScreens,'json');
  });

  form.submit(function(e){
    e.preventDefault();
    const payload = {
      id: +$('#screenId').val(),
      screen_number: +$('#screenNumber').val(),
      ip: $('#screenIp').val(),
      port: +$('#screenPort').val()
    };
    const url = payload.id ? 'php/update_screen.php' : 'php/create_screen.php';
    $.ajax({
      url, method:'POST', contentType:'application/json',
      data: JSON.stringify(payload),
      success(resp){
        if(resp.status==='success'){
          modal.modal('hide');
          loadScreens();
        } else alert(resp.message);
      }
    });
  });

  loadScreens();
});
