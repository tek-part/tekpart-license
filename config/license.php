<?php

return [
    /*
    |--------------------------------------------------------------------------
    | إعدادات الترخيص
    |--------------------------------------------------------------------------
    |
    | هذا الملف يحتوي على إعدادات نظام الترخيص الخاص بشركة Tek-Part
    | يمكنك تعديل هذه الإعدادات حسب احتياجات مشروعك
    |
    */

    // مفتاح الترخيص
    'key' => env('TEKPART_LICENSE_KEY', ''),

    // عنوان خادم التحقق من الترخيص
    'verification_server' => env('TEKPART_VERIFICATION_SERVER', 'https://license.tek-part.com'),

    // هل يتم التحقق من الترخيص عن بعد
    'verify_remotely' => env('TEKPART_VERIFY_REMOTELY', true),

    // معلومات الترخيص
    'owner' => env('TEKPART_LICENSE_OWNER', ''),
    'product' => env('TEKPART_LICENSE_PRODUCT', ''),
    'domain' => env('TEKPART_LICENSE_DOMAIN', '*'),

    // مفاتيح التشفير
    'public_key' => env('TEKPART_PUBLIC_KEY', ''),
    'private_key' => env('TEKPART_PRIVATE_KEY', ''),

    // إعدادات حماية الشفرة المصدرية
    'code_protection' => [
        'enabled' => env('TEKPART_CODE_PROTECTION', true),
        'obfuscate_code' => env('TEKPART_OBFUSCATE_CODE', true),
        'disable_debug' => env('TEKPART_DISABLE_DEBUG', true),
    ],

    // خيارات متقدمة
    'options' => [
        'check_interval' => env('TEKPART_CHECK_INTERVAL', 1440), // بالدقائق (1440 = يوم واحد)
        'grace_period' => env('TEKPART_GRACE_PERIOD', 3), // فترة السماح بعد انتهاء الترخيص (بالأيام)
        'offline_period' => env('TEKPART_OFFLINE_PERIOD', 7), // المدة المسموح بها للعمل دون اتصال (بالأيام)
    ],

    // إعدادات ملف الترخيص
    'license_file' => [
        'path' => env('TEKPART_LICENSE_PATH', storage_path('app/license')),
        'name' => env('TEKPART_LICENSE_FILENAME', 'license.dat'),
    ],
];
