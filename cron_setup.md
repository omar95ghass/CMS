# إعداد Cron Job للتنظيف اليومي

## إضافة Cron Job

لإعداد التنظيف التلقائي للأدوار غير المكتملة، أضف السطر التالي إلى crontab:

```bash
# تنظيف الأدوار غير المكتملة كل يوم في الساعة 6:00 صباحاً
0 6 * * * /usr/bin/php /path/to/your/project/php/daily_cleanup.php >> /var/log/queue_cleanup.log 2>&1
```

## طريقة الإعداد:

### 1. فتح crontab:
```bash
crontab -e
```

### 2. إضافة السطر:
```bash
0 6 * * * /usr/bin/php /path/to/your/project/php/daily_cleanup.php >> /var/log/queue_cleanup.log 2>&1
```

### 3. حفظ الملف والخروج

## بديل: استخدام wget/curl

إذا لم يكن PHP متاحاً في cron، يمكن استخدام:

```bash
# كل يوم في الساعة 6:00 صباحاً
0 6 * * * wget -q -O /dev/null http://yourdomain.com/php/daily_cleanup.php
```

## اختبار التنظيف يدوياً:

```bash
php /path/to/your/project/php/daily_cleanup.php
```

## مراقبة السجلات:

```bash
tail -f /var/log/queue_cleanup.log
```

## ملاحظات:

- تأكد من أن المسار صحيح
- تأكد من صلاحيات الكتابة في مجلد logs
- يمكن تغيير وقت التنظيف حسب الحاجة