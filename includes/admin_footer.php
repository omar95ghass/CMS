            </div>
        </div>
    </div>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    
    <script>
        // تحديث البيانات كل 30 ثانية
        setInterval(function() {
            if (typeof refreshData === 'function') {
                refreshData();
            }
        }, 30000);
        
        // إظهار رسائل التنبيه
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // تحميل البيانات مع معالجة الأخطاء
        function loadData(url, callback) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        callback(data);
                    } else {
                        showNotification('خطأ في تحميل البيانات: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    showNotification('حدث خطأ في تحميل البيانات', 'danger');
                });
        }
        
        // تنسيق الأرقام
        function formatNumber(num) {
            return new Intl.NumberFormat('ar-SA').format(num);
        }
        
        // تنسيق الوقت
        function formatTime(minutes) {
            if (minutes < 60) {
                return minutes + ' دقيقة';
            } else {
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                return hours + ' ساعة ' + (mins > 0 ? mins + ' دقيقة' : '');
            }
        }
        
        // تحديث الوقت الحالي
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ar-SA', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('ar-SA', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.innerHTML = `${timeString} - ${dateString}`;
            }
        }
        
        // تحديث الوقت كل ثانية
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
    </script>
    
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>