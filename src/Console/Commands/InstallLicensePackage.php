<?php

namespace TekPart\License\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TekPart\License\Facades\License;

class InstallLicensePackage extends Command
{
    /**
     * اسم الأمر ووصفه.
     *
     * @var string
     */
    protected $signature = 'tekpart:install-license';

    /**
     * وصف الأمر.
     *
     * @var string
     */
    protected $description = 'تثبيت وإعداد باكدج التراخيص من Tek-Part';

    /**
     * تنفيذ الأمر.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('تثبيت نظام التراخيص من Tek-Part...');

        // إنشاء مجلد التراخيص
        $licensePath = storage_path('app/license');
        if (!File::exists($licensePath)) {
            File::makeDirectory($licensePath, 0755, true);
            $this->info('تم إنشاء مجلد التراخيص: ' . $licensePath);
        }

        // توليد زوج المفاتيح
        $generateKeys = $this->confirm('هل تريد توليد زوج مفاتيح تشفير جديد؟');
        if ($generateKeys) {
            $keys = License::generateKeyPair();
            $this->info('تم توليد مفاتيح التشفير بنجاح!');

            // حفظ المفاتيح في ملفات
            File::put($licensePath . '/public_key.pem', $keys['public']);
            File::put($licensePath . '/private_key.pem', $keys['private']);
            $this->info('تم حفظ المفاتيح في مجلد التراخيص');

            // إضافة المفاتيح إلى ملف .env
            $this->info('يرجى إضافة المفاتيح التالية إلى ملف .env الخاص بك:');
            $this->line('TEKPART_PUBLIC_KEY="' . $keys['public'] . '"');
            $this->line('TEKPART_PRIVATE_KEY="' . $keys['private'] . '"');
        }

        // طلب معلومات الترخيص
        $owner = $this->ask('اسم مالك الترخيص (اسم الشركة):');
        $domain = $this->ask('النطاق (domain) المرخص (استخدم * للسماح بجميع النطاقات):', '*');
        $product = $this->ask('اسم المنتج المرخص:');

        // إضافة معلومات الترخيص إلى ملف .env
        $this->info('يرجى إضافة المعلومات التالية إلى ملف .env الخاص بك:');
        $this->line('TEKPART_LICENSE_OWNER="' . $owner . '"');
        $this->line('TEKPART_LICENSE_DOMAIN="' . $domain . '"');
        $this->line('TEKPART_LICENSE_PRODUCT="' . $product . '"');

        $this->info('لتوليد ترخيص جديد، استخدم الأمر: php artisan tekpart:generate-license');

        $this->info('تم تثبيت نظام التراخيص بنجاح!');

        return 0;
    }
}
