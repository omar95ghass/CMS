console.log("main.js loaded");

$(document).ready(function() {
    // console.log("Document is ready");

    $('#callNext').on('click', function() {
        console.log("callNext button clicked");
        $.get('php/call_next.php', function(data) {
            console.log("Response from call_next.php:", data);
            if (data.status === 'success') {
                alert('تم نداء الرقم: ' + data.number);
            } else {
                alert('لا توجد أرقام في الدور');
            }
        }, 'json');

        fetchQueue();
        setInterval(fetchQueue, 5000); // تحديث القائمة كل 5 ثواني
    });

    $('#callSpecific').on('click', function() {
        let number = prompt('أدخل الرقم الذي تريد النداء عليه:');
        if (number) {
            $.post('php/accountant_call.php', { number: number }, function(data) {
                console.log("Response from accountant_call.php:", data);
                if (data.status === 'success') {
                    alert('تم نداء الرقم: ' + number);
                } else {
                    alert('لم يتم العثور على الرقم');
                }
            }, 'json');
        }
    });

    // Load initial queue for the accountant
    fetchQueue();
});

function fetchQueue() {
    // console.log("Fetching queue...");
    $.get('php/get_queue.php', function(data) {
        console.log("Response from get_queue.php:", data);
        if (data.status === 'success') {
            const container = $('#accountantQueue');
            container.empty();
            data.data.forEach(item => {
                let statusClass = '';
                switch(item.status) {
                    case 'waiting':
                        statusClass = 'bg-light-red';
                        break;
                    case 'called':
                        statusClass = 'bg-light-yellow';
                        break;
                    case 'announced':
                        statusClass = 'bg-light-yellow';
                        break;
                    case 'completed':
                        statusClass = 'bg-light-green';
                        break;
                }
                let listItem = `<div class="list-group-item ${statusClass}">
                    ${item.clinic} - ${item.number}
                    ${item.status === 'waiting' || item.status === 'called' || item.status === 'announced' ? 
                        `<button class="btn btn-primary btn-sm float-right ml-2 call-btn" data-number="${item.number}">نداء</button>` 
                    : ''}
                    ${item.status === 'called' || item.status === 'announced' ? `<button class="btn btn-success btn-sm float-right complete-btn" data-id="${item.number}">استلام</button>` : ''}
                </div>`;
                container.append(listItem);
            });

            $('.complete-btn').on('click', function() {
                let id = $(this).data('id');
                $.post('php/update_status.php', { id: id, status: 'completed' }, function(response) {
                    if (response.status === 'success') {
                        fetchQueue(); // Refresh the queue after updating
                    } else {
                        alert('فشل في تحديث الحالة: ' + response.message);
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error updating status:', textStatus, errorThrown);
                });
            });

            $('.call-btn').on('click', function() {
                const number = $(this).data('number');
                // console.log(number);
                callSpecificNumber(number);
            });

        } else {
            alert('فشل في جلب الأدوار: ' + data.message);
        }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Error fetching queue:', textStatus, errorThrown);
    });
}

function callSpecificNumber(number) {
    // console.log(number);
    $.ajax({
        url: 'php/call_specific.php',
        type: 'POST',
        data: { number: number },
        dataType: 'text', // نقرأ كنص خام أولًا
        success: function(text) {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // الرد ليس JSON صالح
                console.error('Raw response from call_specific.php:', text);
                return alert(
                    'فشل في فك JSON من الخادم:\n' + e.message +
                    '\n\nRaw server response:\n' + text
                );
            }
            // الآن data هو JSON صالحة
            if (data.status === 'success') {
                fetchQueue();
            } else {
                alert('فشل في نداء الدور: ' + data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            const raw = jqXHR.responseText;
            console.error('Error in AJAX call_specific.php:', textStatus, errorThrown, raw);
            alert(
                'خطأ في الاتصال بـ call_specific.php\n' +
                'Status: ' + textStatus + '\n' +
                'Error: ' + errorThrown + '\n\n' +
                'Response Text:\n' + raw
            );
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
      fetchQueue();
      setInterval(fetchQueue, 5000);
  });

