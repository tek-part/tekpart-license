# TekPart License System

نظام حماية الشفرة المصدرية وإدارة التراخيص لمشاريع Laravel من شركة Tek-Part.

## الميزات

* إدارة التراخيص وحماية الشفرة المصدرية
* التحقق من صلاحية الترخيص محليًا وعن بعد
* دعم التراخيص المحدودة بالنطاق أو التاريخ
* واجهة سهلة الاستخدام لإدارة التراخيص
* أوامر مساعدة لإنشاء وإدارة التراخيص
* نظام تشفير قوي لملفات الترخيص
* دعم وضع العمل دون اتصال

## متطلبات النظام

* PHP 7.4 أو أعلى
* Laravel 8.x أو أعلى
* مكتبة phpseclib v3 للتشفير

## التثبيت

### باستخدام Composer

```bash
composer require tekpart/license
```

### نشر ملفات الإعدادات والترحيلات

```bash
php artisan vendor:publish --provider="TekPart\License\LicenseServiceProvider"
```

### تشغيل الترحيلات

```bash
php artisan migrate
```

### تثبيت الباكدج

```bash
php artisan tekpart:install-license
```

## الاستخدام الأساسي

### التحقق من صلاحية الترخيص

```php
use TekPart\License\Facades\License;

// التحقق من صلاحية الترخيص
if (License::verifyLicense()) {
    // الترخيص صالح
} else {
    // الترخيص غير صالح أو منتهي الصلاحية
}
```

### استخدام الوسيط (Middleware)

يمكنك حماية المسارات أو المنطقة الإدارية باستخدام الوسيط المضمن:

```php
// في ملف routes/web.php
Route::middleware('license.check')->group(function () {
    // المسارات المحمية بالترخيص
    Route::get('/admin', 'AdminController@index');
});
```

### توليد ترخيص جديد

```bash
php artisan tekpart:generate-license
```

أو مع تحديد الخيارات مباشرة:

```bash
php artisan tekpart:generate-license --domain=example.com --expires=2023-12-31 --owner="اسم الشركة" --email=email@example.com
```

## واجهة برمجة التطبيقات (API)

### التحقق من الترخيص

```
GET /license/status
```

### تفعيل الترخيص

```
POST /license/activate
```

مع البيانات:
- `license_key`: مفتاح الترخيص
- `license_file`: ملف الترخيص (اختياري)

## الأمان

يستخدم النظام تشفير RSA مع مفاتيح بطول 2048 بت لحماية ملفات الترخيص.

## الدعم الفني

للدعم الفني، يرجى التواصل مع فريق Tek-Part على البريد الإلكتروني: support@tekpart.com

## الترخيص

محمي بحقوق الملكية، جميع الحقوق محفوظة لشركة Tek-Part. 
