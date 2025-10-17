        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> نظام إدارة الطوابير - جميع الحقوق محفوظة</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>الإصدار 2.0 | تم التطوير بواسطة فريق التطوير</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="css/bootstrap/jQuery/jquery-3.6.0.min.js"></script>
    <script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- رسائل التنبيه العامة -->
    <div id="globalAlert" class="alert alert-dismissible fade" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: none;">
        <span id="alertMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <script>
        // دالة عرض التنبيهات العامة
        function showGlobalAlert(message, type = 'info') {
            const alert = document.getElementById('globalAlert');
            const messageEl = document.getElementById('alertMessage');
            
            // إزالة جميع الكلاسات السابقة
            alert.className = 'alert alert-dismissible fade';
            
            // إضافة الكلاس المناسب
            alert.classList.add('alert-' + type);
            
            // تعيين الرسالة
            messageEl.textContent = message;
            
            // إظهار التنبيه
            alert.style.display = 'block';
            alert.classList.add('show');
            
            // إخفاء التنبيه تلقائياً بعد 5 ثوان
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 150);
            }, 5000);
        }

        // دالة تحديث الوقت
        function updateTime() {
            const now = new Date();
            const timeElements = document.querySelectorAll('.current-time');
            timeElements.forEach(el => {
                el.textContent = now.toLocaleString('ar-SA');
            });
        }

        // تحديث الوقت كل ثانية
        setInterval(updateTime, 1000);
        updateTime();

        // إضافة تأثيرات تفاعلية للأزرار
        document.addEventListener('DOMContentLoaded', function() {
            // تأثير hover للأزرار
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'all 0.3s ease';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // تأثير hover للكروت
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'all 0.3s ease';
                    this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                });
            });
        });

        // دالة تأكيد الحذف
        function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
            return confirm(message);
        }

        // دالة تأكيد الإجراء
        function confirmAction(message = 'هل أنت متأكد من هذا الإجراء؟') {
            return confirm(message);
        }
    </script>
</body>
</html>