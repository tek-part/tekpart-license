<?php

namespace TekPart\License;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use phpseclib3\Crypt\RSA;

class License
{
    /**
     * مفتاح الترخيص
     *
     * @var string
     */
    protected $licenseKey;

    /**
     * عنوان URL الخاص بسيرفر التحقق من الترخيص
     *
     * @var string
     */
    protected $verificationServer;

    /**
     * إنشاء كائن جديد.
     *
     * @return void
     */
    public function __construct()
    {
        $this->licenseKey = config('tekpart.license.key');
        $this->verificationServer = config('tekpart.license.verification_server');
    }

    /**
     * التحقق من صلاحية الترخيص
     *
     * @return bool
     */
    public function verifyLicense()
    {
        // إذا كانت عملية التحقق مخزنة في الكاش، استخدمها
        if (Cache::has('license_valid')) {
            return Cache::get('license_valid');
        }

        // الحصول على معلومات النظام
        $systemInfo = $this->getSystemInfo();

        // التحقق من الترخيص محليًا
        $localVerification = $this->verifyLocalLicense($systemInfo);

        // التحقق من الترخيص عن بعد إذا كان ذلك مطلوبًا
        $remoteVerification = true;
        if (config('tekpart.license.verify_remotely')) {
            $remoteVerification = $this->verifyRemoteLicense($systemInfo);
        }

        $isValid = $localVerification && $remoteVerification;

        // تخزين النتيجة في الكاش لمدة يوم
        Cache::put('license_valid', $isValid, now()->addDay());

        return $isValid;
    }

    /**
     * التحقق من الترخيص محليًا
     *
     * @param array $systemInfo
     * @return bool
     */
    protected function verifyLocalLicense($systemInfo)
    {
        // التحقق من وجود ملف الترخيص
        $licensePath = storage_path('app/license/license.dat');
        if (!File::exists($licensePath)) {
            return false;
        }

        // قراءة ملف الترخيص
        $licenseData = File::get($licensePath);

        // فك تشفير الترخيص
        $licenseData = $this->decryptLicense($licenseData);
        if (!$licenseData) {
            return false;
        }

        // تحويل بيانات الترخيص إلى كائن PHP
        $license = json_decode($licenseData, true);
        if (!$license) {
            return false;
        }

        // التحقق من صلاحية الترخيص
        if (!isset($license['domain']) || !isset($license['expiration'])) {
            return false;
        }

        // التحقق من تاريخ انتهاء الصلاحية
        if (strtotime($license['expiration']) < time()) {
            return false;
        }

        // التحقق من النطاق
        $currentDomain = $systemInfo['domain'];
        if ($license['domain'] !== '*' && $license['domain'] !== $currentDomain) {
            return false;
        }

        return true;
    }

    /**
     * التحقق من الترخيص على الخادم البعيد
     *
     * @param array $systemInfo
     * @return bool
     */
    protected function verifyRemoteLicense($systemInfo)
    {
        try {
            // إرسال طلب للتحقق من الترخيص
            $response = Http::post($this->verificationServer . '/api/verify-license', [
                'license_key' => $this->licenseKey,
                'domain' => $systemInfo['domain'],
                'ip' => $systemInfo['ip'],
                'app_version' => $systemInfo['app_version'],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return isset($result['valid']) && $result['valid'] === true;
            }
        } catch (\Exception $e) {
            // في حالة وجود خطأ في الاتصال، نرجع إلى التحقق المحلي فقط
            \Log::error('Remote license verification failed: ' . $e->getMessage());
        }

        // إذا لم نتمكن من الاتصال بالخادم، نعتبر أن التحقق عن بعد ناجح
        // ونعتمد على التحقق المحلي فقط
        return true;
    }

    /**
     * الحصول على معلومات النظام
     *
     * @return array
     */
    protected function getSystemInfo()
    {
        return [
            'domain' => request()->getHost(),
            'ip' => request()->ip(),
            'app_version' => config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
        ];
    }

    /**
     * فك تشفير بيانات الترخيص
     *
     * @param string $encryptedData
     * @return string|false
     */
    protected function decryptLicense($encryptedData)
    {
        try {
            // استخدام المفتاح العام للتحقق من التوقيع
            $publicKey = config('tekpart.license.public_key');
            if (!$publicKey) {
                return false;
            }

            // تهيئة RSA
            $rsa = RSA::loadPublicKey($publicKey);

            // فك التشفير
            list($signature, $data) = explode('|', base64_decode($encryptedData), 2);

            // التحقق من التوقيع
            if ($rsa->verify($data, base64_decode($signature))) {
                return $data;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('License decryption failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إنشاء ترخيص جديد
     *
     * @param array $licenseData
     * @return string
     */
    public function generateLicense($licenseData)
    {
        // التأكد من وجود المفتاح الخاص
        $privateKey = config('tekpart.license.private_key');
        if (!$privateKey) {
            throw new \Exception('Private key is missing');
        }

        // تحويل بيانات الترخيص إلى JSON
        $licenseJson = json_encode($licenseData);

        // تهيئة RSA
        $rsa = RSA::loadPrivateKey($privateKey);

        // توقيع البيانات
        $signature = base64_encode($rsa->sign($licenseJson));

        // دمج التوقيع مع البيانات وتشفيرها
        $encryptedData = base64_encode($signature . '|' . $licenseJson);

        return $encryptedData;
    }

    /**
     * توليد زوج مفاتيح جديد للتشفير
     *
     * @return array
     */
    public function generateKeyPair()
    {
        $rsa = RSA::createKey(2048);

        return [
            'private' => $rsa->toString('PKCS8'),
            'public' => $rsa->getPublicKey()->toString('PKCS8'),
        ];
    }

    /**
     * فك تشفير بيانات الترخيص (واجهة عامة)
     *
     * @param string $encryptedData
     * @return string|false
     */
    public function decrypt($encryptedData)
    {
        return $this->decryptLicense($encryptedData);
    }
}
