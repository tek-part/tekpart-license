<?php

namespace TekPart\License\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use TekPart\License\Facades\License;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class GenerateLicenseCommand extends Command
{
    /**
     * اسم الأمر ووصفه.
     *
     * @var string
     */
    protected $signature = 'tekpart:generate-license
                            {--domain= : النطاق (domain) الذي سيتم ترخيصه}
                            {--expires= : تاريخ انتهاء الترخيص (YYYY-MM-DD)}
                            {--owner= : اسم مالك الترخيص}
                            {--email= : البريد الإلكتروني لمالك الترخيص}
                            {--product= : اسم المنتج المرخص}
                            {--output= : مسار حفظ ملف الترخيص}';

    /**
     * وصف الأمر.
     *
     * @var string
     */
    protected $description = 'توليد ملف ترخيص جديد للمشروع';

    /**
     * تنفيذ الأمر.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('توليد ترخيص جديد من Tek-Part...');

        // التحقق من وجود مفتاح التشفير
        $privateKey = config('tekpart.license.private_key');
        if (!$privateKey) {
            $this->error('المفتاح الخاص غير موجود! قم بتنفيذ الأمر tekpart:install-license أولاً.');
            return 1;
        }

        // جمع معلومات الترخيص
        $domain = $this->option('domain') ?: $this->ask('النطاق (domain) المرخص (استخدم * للسماح بجميع النطاقات):', '*');
        $expiresAt = $this->option('expires') ?: $this->ask('تاريخ انتهاء الترخيص (YYYY-MM-DD):', date('Y-m-d', strtotime('+1 year')));
        $owner = $this->option('owner') ?: $this->ask('اسم مالك الترخيص:', config('tekpart.license.owner', ''));
        $email = $this->option('email') ?: $this->ask('البريد الإلكتروني لمالك الترخيص:');
        $product = $this->option('product') ?: $this->ask('اسم المنتج المرخص:', config('tekpart.license.product', ''));

        // توليد مفتاح الترخيص
        $licenseKey = Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4);

        // إنشاء بيانات الترخيص
        $licenseData = [
            'key' => $licenseKey,
            'domain' => $domain,
            'owner' => $owner,
            'email' => $email,
            'product' => $product,
            'issued_at' => date('Y-m-d'),
            'expiration' => $expiresAt,
            'features' => [
                'basic' => true,
                'premium' => true,
            ],
            'hash' => md5($licenseKey . $domain . $expiresAt),
        ];

        // توليد ملف الترخيص
        try {
            $licenseContent = License::generateLicense($licenseData);

            // تحديد مسار الحفظ
            $outputPath = $this->option('output') ?: storage_path('app/license/license.dat');

            // التأكد من وجود المجلد
            $directory = dirname($outputPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // حفظ ملف الترخيص
            File::put($outputPath, $licenseContent);

            $this->info('تم توليد ملف الترخيص بنجاح وحفظه في: ' . $outputPath);
            $this->line('مفتاح الترخيص الخاص بك هو: ' . $licenseKey);
            $this->line('تاريخ الانتهاء: ' . $expiresAt);

            // حفظ في قاعدة البيانات إذا أمكن
            if (Schema::hasTable('tekpart_licenses')) {
                DB::table('tekpart_licenses')->insert([
                    'license_key' => $licenseKey,
                    'product_name' => $product,
                    'customer_name' => $owner,
                    'customer_email' => $email,
                    'domain' => $domain,
                    'issued_at' => date('Y-m-d'),
                    'expires_at' => $expiresAt,
                    'is_active' => true,
                    'features' => json_encode($licenseData['features']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info('تم حفظ معلومات الترخيص في قاعدة البيانات.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء توليد الترخيص: ' . $e->getMessage());
            return 1;
        }
    }
}