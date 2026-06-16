# منصة عسير

منصة Laravel لإدارة وحجز تذاكر الفعاليات، بواجهة عربية RTL، لوحة إدارة متكاملة، لوحة منظم، تدفق حجز، تذاكر QR/Barcode، تصدير PDF/XLSX، وإدارة محتوى وفوتر ديناميكية.

## المتطلبات

- PHP 8.2 أو أحدث
- MySQL 8 أو MariaDB متوافق
- Composer 2
- امتدادات PHP: `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`, `zip`, `fileinfo`
- صلاحية كتابة على `storage` و `bootstrap/cache`

## أهم الوحدات

- الصفحة الرئيسية والبنرات والإعلانات من لوحة التحكم.
- إدارة الفعاليات والتذاكر والأسعار والصور.
- إدارة الحجوزات والمدفوعات وإعادة إرسال التذاكر.
- تذاكر قابلة للطباعة مع QR و Barcode و PDF.
- إدارة المستخدمين والمنظمين والصلاحيات الأساسية.
- إدارة التصنيفات والمدن والمواقع.
- إدارة الكوبونات والتقارير والتصدير CSV/XLSX/PDF.
- CMS للصفحات الثابتة والأسئلة الشائعة وروابط الفوتر.
- Activity Log للإجراءات الإدارية المهمة.

## التشغيل المحلي

```powershell
C:\xampp\php\php.exe composer.phar install
Copy-Item .env.example .env
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan serve
```

ثم افتح:

```text
http://127.0.0.1:8000
```

## حسابات تجريبية

- Admin: `admin@farah.sa` / `password`
- Organizer: `organizer@farah.sa` / `password`
- Customer: `customer@farah.sa` / `password`

## تجهيز الإنتاج

حدّث ملف `.env` على السيرفر:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls

QUEUE_CONNECTION=database
SESSION_ENCRYPT=true

STRIPE_KEY=...
STRIPE_SECRET=...
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
MADA_MERCHANT_ID=...
```

أوامر النشر:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:work
```

يجب توجيه Document Root إلى مجلد `public`.

## فحص قبل النشر

```bash
php artisan about
php artisan migrate:status
php artisan route:list
php artisan view:cache
php artisan config:cache
php artisan route:cache
vendor/bin/phpunit
```

## ملاحظات إنتاجية

- لا ترفع ملف `.env` إلى Git.
- لا ترفع ملفات debug أو `composer.phar` أو `composer-setup.php`.
- فعّل SMTP حقيقي حتى تصل التذاكر بالبريد.
- فعّل مفاتيح الدفع الحقيقية قبل استقبال مدفوعات فعلية.
- شغّل queue worker أو Supervisor في الإنتاج لمعالجة المهام الطويلة.
- تأكد من صلاحيات `storage` و `bootstrap/cache`.
