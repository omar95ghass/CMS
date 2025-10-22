# إعداد Cron Jobs للنظام

## 1. إعداد Cron Job للتنظيف اليومي

لإعداد التنظيف التلقائي للأدوار غير المكتملة، أضف السطر التالي إلى crontab:

```bash
# تنظيف الأدوار غير المكتملة كل يوم في الساعة 6:00 صباحاً
0 6 * * * /usr/bin/php /path/to/your/project/php/daily_cleanup.php >> /var/log/queue_cleanup.log 2>&1
```

## 2. إعداد Cron Job للمزامنة التلقائية

لإعداد المزامنة التلقائية بين قاعدتي البيانات، أضف السطر التالي:

```bash
# مزامنة قواعد البيانات كل 5 دقائق
*/5 * * * * /usr/bin/php /path/to/your/project/php/cron_sync.php >> /var/log/queue_sync.log 2>&1
```

## طريقة الإعداد:

### 1. فتح crontab:
```bash
crontab -e
```

### 2. إضافة الأسطر:
```bash
# تنظيف الأدوار غير المكتملة كل يوم في الساعة 6:00 صباحاً
0 6 * * * /usr/bin/php /path/to/your/project/php/daily_cleanup.php >> /var/log/queue_cleanup.log 2>&1

# مزامنة قواعد البيانات كل 5 دقائق
*/5 * * * * /usr/bin/php /path/to/your/project/php/cron_sync.php >> /var/log/queue_sync.log 2>&1
```

### 3. حفظ الملف والخروج

## بديل: استخدام wget/curl

إذا لم يكن PHP متاحاً في cron، يمكن استخدام:

```bash
# كل يوم في الساعة 6:00 صباحاً
0 6 * * * wget -q -O /dev/null http://yourdomain.com/php/daily_cleanup.php
```

## اختبار المهام يدوياً:

```bash
# اختبار التنظيف
php /path/to/your/project/php/daily_cleanup.php

# اختبار المزامنة
php /path/to/your/project/php/cron_sync.php
```

## مراقبة السجلات:

```bash
# مراقبة سجل التنظيف
tail -f /var/log/queue_cleanup.log

# مراقبة سجل المزامنة
tail -f /var/log/queue_sync.log

# مراقبة سجل أخطاء قاعدة البيانات
tail -f /path/to/your/project/logs/db_errors.log
```

## ملاحظات:

- تأكد من أن المسار صحيح
- تأكد من صلاحيات الكتابة في مجلد logs
- يمكن تغيير وقت التنظيف حسب الحاجة