<?php

namespace TekPart\License;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use TekPart\License\Console\Commands\InstallLicensePackage;
use TekPart\License\Console\Commands\GenerateLicenseCommand;
use TekPart\License\Middleware\LicenseCheck;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * تسجيل أي خدمات الباكدج.
     *
     * @return void
     */
    public function register()
    {
        // دمج إعدادات الباكدج
        $this->mergeConfigFrom(
            __DIR__.'/../config/license.php', 'tekpart.license'
        );

        // تسجيل واجهة الباكدج
        $this->app->singleton('teklicense', function ($app) {
            return new License();
        });

        // تسجيل الوسيط (middleware)
        $this->app['router']->aliasMiddleware('license.check', LicenseCheck::class);
    }

    /**
     * تهيئة خدمات الباكدج.
     *
     * @return void
     */
    public function boot()
    {
        // نشر الإعدادات
        $this->publishes([
            __DIR__.'/../config/license.php' => config_path('tekpart/license.php'),
        ], 'tekpart-license-config');

        // نشر الترحيلات
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'tekpart-license-migrations');

        // تحميل الترحيلات
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // تحميل العروض
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teklicense');

        // نشر العروض
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/teklicense'),
        ], 'tekpart-license-views');

        // تحميل المسارات
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // تسجيل أوامر الـ console
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallLicensePackage::class,
                GenerateLicenseCommand::class,
            ]);
        }
    }
}
