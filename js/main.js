console.log("main.js loaded");

$(document).ready(function() {
    // تهيئة النظام
    initializeSystem();
    
    // تحميل قائمة الأدوار الأولية
    fetchQueue();
    
    // تحديث قائمة الأدوار كل 5 ثوان
    setInterval(fetchQueue, 5000);
});

function initializeSystem() {
    // إعداد معالجات الأحداث
    setupEventHandlers();
    
    // إعداد التنبيهات
    setupNotifications();
    
    // إعداد التحديث التلقائي
    setupAutoRefresh();
}

function setupEventHandlers() {
    // معالج نداء الدور التالي
    $('#callNext').on('click', function() {
        callNextNumber();
    });

    // معالج النداء المحدد
    $('#callSpecific').on('click', function() {
        showSpecificCallModal();
    });
    
    // معالج تحديث الحالة
    $(document).on('click', '.complete-btn', function() {
        const id = $(this).data('id');
        completeService(id);
    });
    
    // معالج نداء دور محدد من القائمة
    $(document).on('click', '.call-btn', function() {
        const number = $(this).data('number');
        callSpecificNumber(number);
    });
}

function setupNotifications() {
    // إعداد نظام التنبيهات
    if (!window.Notification) {
        console.log("This browser does not support notifications");
        return;
    }
    
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function setupAutoRefresh() {
    // تحديث البيانات كل 30 ثانية
    setInterval(() => {
        updateSystemStats();
    }, 30000);
}

function callNextNumber() {
    showLoading('#callNext');
    
    $.get('php/call_next.php')
        .done(function(data) {
            if (data.status === 'success') {
                showNotification('تم نداء الرقم: ' + data.number, 'success');
                fetchQueue();
            } else {
                showNotification(data.message || 'لا توجد أرقام في الدور', 'warning');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error calling next number:', error);
            showNotification('خطأ في نداء الدور التالي', 'error');
        })
        .always(function() {
            hideLoading('#callNext');
        });
}

function showSpecificCallModal() {
    const number = prompt('أدخل الرقم الذي تريد النداء عليه:');
    if (number && !isNaN(number)) {
        callSpecificNumber(parseInt(number));
    } else if (number) {
        showNotification('يرجى إدخال رقم صحيح', 'warning');
    }
}

function callSpecificNumber(number) {
    if (!number || isNaN(number)) {
        showNotification('رقم غير صحيح', 'warning');
        return;
    }
    
    showLoading('.call-btn[data-number="' + number + '"]');
    
    $.ajax({
        url: 'php/call_specific.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ number: number }),
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'success') {
            showNotification('تم نداء الرقم: ' + number, 'success');
            fetchQueue();
        } else {
            showNotification(data.message || 'لم يتم العثور على الرقم', 'error');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Error calling specific number:', error);
        showNotification('خطأ في نداء الرقم المحدد', 'error');
    })
    .always(function() {
        hideLoading('.call-btn[data-number="' + number + '"]');
    });
}

function completeService(id) {
    if (!confirm('هل أنت متأكد من إنهاء هذه الخدمة؟')) {
        return;
    }
    
    showLoading('.complete-btn[data-id="' + id + '"]');
    
    $.post('php/update_status.php', { 
        id: id, 
        status: 'completed' 
    })
    .done(function(response) {
        if (response.status === 'success') {
            showNotification('تم إنهاء الخدمة بنجاح', 'success');
            fetchQueue();
        } else {
            showNotification('فشل في تحديث الحالة: ' + response.message, 'error');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Error updating status:', error);
        showNotification('خطأ في تحديث الحالة', 'error');
    })
    .always(function() {
        hideLoading('.complete-btn[data-id="' + id + '"]');
    });
}

function fetchQueue() {
    $.get('php/get_queue.php')
        .done(function(data) {
            if (data.status === 'success') {
                displayQueue(data.data);
                updateQueueStats(data.data);
            } else {
                console.error('Error fetching queue:', data.message);
                showNotification('فشل في جلب قائمة الأدوار: ' + data.message, 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error fetching queue:', error);
            showNotification('خطأ في الاتصال بالخادم', 'error');
        });
}

function displayQueue(queueData) {
    const container = $('#accountantQueue');
    container.empty();
    
    if (!queueData || queueData.length === 0) {
        container.html('<div class="list-group-item text-center text-muted">لا توجد أدوار في الانتظار</div>');
        return;
    }
    
    queueData.forEach(item => {
        const statusInfo = getStatusInfo(item.status);
        const waitingTime = calculateWaitingTime(item.created_at);
        
        const listItem = $(`
            <div class="list-group-item ${statusInfo.class}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.clinic}</strong> - رقم ${item.number}
                        <br>
                        <small class="text-muted">
                            ${statusInfo.text} 
                            ${waitingTime ? `- ${waitingTime}` : ''}
                        </small>
                    </div>
                    <div>
                        ${getActionButtons(item)}
                    </div>
                </div>
            </div>
        `);
        
        container.append(listItem);
    });
}

function getStatusInfo(status) {
    const statusMap = {
        'waiting': { class: 'list-group-item-warning', text: 'في الانتظار' },
        'called': { class: 'list-group-item-info', text: 'مدعو' },
        'announced': { class: 'list-group-item-primary', text: 'تم الإعلان' },
        'completed': { class: 'list-group-item-success', text: 'مكتمل' }
    };
    
    return statusMap[status] || { class: '', text: status };
}

function getActionButtons(item) {
    let buttons = '';
    
    if (item.status === 'waiting') {
        buttons += `<button class="btn btn-primary btn-sm call-btn" data-number="${item.number}">
            <i class="fas fa-bullhorn"></i> نداء
        </button>`;
    }
    
    if (item.status === 'called' || item.status === 'announced') {
        buttons += `<button class="btn btn-success btn-sm complete-btn" data-id="${item.id}">
            <i class="fas fa-check"></i> استلام
        </button>`;
    }
    
    return buttons;
}

function calculateWaitingTime(createdAt) {
    const now = new Date();
    const created = new Date(createdAt);
    const diff = Math.floor((now - created) / 60000); // بالدقائق
    
    if (diff < 60) {
        return `${diff} دقيقة`;
    } else {
        const hours = Math.floor(diff / 60);
        const minutes = diff % 60;
        return `${hours}س ${minutes}د`;
    }
}

function updateQueueStats(queueData) {
    const stats = {
        total: queueData.length,
        waiting: queueData.filter(item => item.status === 'waiting').length,
        called: queueData.filter(item => item.status === 'called').length,
        completed: queueData.filter(item => item.status === 'completed').length
    };
    
    // تحديث الإحصائيات في الواجهة إذا كانت موجودة
    if (typeof updateStatsDisplay === 'function') {
        updateStatsDisplay(stats);
    }
}

function updateSystemStats() {
    // جلب إحصائيات النظام
    $.get('php/statics.php')
        .done(function(data) {
            if (data.status === 'success') {
                // تحديث الإحصائيات في الواجهة
                console.log('System stats updated:', data);
            }
        })
        .fail(function(error) {
            console.error('Error updating system stats:', error);
        });
}

// دوال مساعدة للواجهة
function showLoading(selector) {
    const element = $(selector);
    element.prop('disabled', true);
    element.data('original-text', element.html());
    element.html('<i class="fas fa-spinner fa-spin"></i> جاري المعالجة...');
}

function hideLoading(selector) {
    const element = $(selector);
    element.prop('disabled', false);
    element.html(element.data('original-text') || element.html());
}

function showNotification(message, type = 'info') {
    // استخدام نظام التنبيهات المدمج إذا كان متاحاً
    if (typeof showGlobalAlert === 'function') {
        showGlobalAlert(message, type);
        return;
    }
    
    // استخدام التنبيهات الأصلية كبديل
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // إضافة التنبيه إلى أعلى الصفحة
    $('body').prepend(alertHtml);
    
    // إزالة التنبيه تلقائياً بعد 5 ثوان
    setTimeout(() => {
        $('.alert').fadeOut(() => {
            $('.alert').remove();
        });
    }, 5000);
}

function showBrowserNotification(title, message, icon = null) {
    if (!window.Notification) {
        return;
    }
    
    if (Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: message,
            icon: icon || 'images/logo/logo.png',
            dir: 'rtl'
        });
        
        // إغلاق التنبيه تلقائياً بعد 5 ثوان
        setTimeout(() => {
            notification.close();
        }, 5000);
    }
}

// دالة تسجيل الأخطاء
function logError(error, context = '') {
    console.error('Error in ' + context + ':', error);
    
    // إرسال تقرير الخطأ إلى الخادم
    if (typeof error !== 'string') {
        error = error.message || 'Unknown error';
    }
    
    $.post('php/log_error.php', {
        error_type: 'javascript_error',
        error_message: error,
        context: context,
        url: window.location.href,
        user_agent: navigator.userAgent
    }).fail(function() {
        console.error('Failed to log error to server');
    });
}

// معالج الأخطاء العام
window.addEventListener('error', function(event) {
    logError(event.error, 'Global Error Handler');
});

// معالج الأخطاء غير المعالجة في Promise
window.addEventListener('unhandledrejection', function(event) {
    logError(event.reason, 'Unhandled Promise Rejection');
});

// تهيئة النظام عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    console.log('System initialized');
    
    // تحميل البيانات الأولية
    fetchQueue();
    
    // إعداد التحديث التلقائي
    setInterval(fetchQueue, 5000);
    
    // إعداد تحديث الإحصائيات
    setInterval(updateSystemStats, 30000);
});

