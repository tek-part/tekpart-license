<?php

namespace TekPart\License;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use phpseclib3\Crypt\RSA;
use TekPart\License\Http\LicenseClient;

class License
{
    /**
     * License key
     *
     * @var string
     */
    protected $licenseKey;

    /**
     * Verification server URL
     *
     * @var string
     */
    protected $verificationServer;

    /**
     * License client instance
     *
     * @var \TekPart\License\Http\LicenseClient
     */
    protected $client;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->licenseKey = config('tekpart.license.key');
        $this->verificationServer = config('tekpart.license.verification_server');
        $this->client = new LicenseClient();
    }

    /**
     * Verify license validity
     *
     * @return bool
     */
    public function verifyLicense()
    {
        // If verification result is cached, use it
        if (Cache::has('license_valid')) {
            return Cache::get('license_valid');
        }

        // Get system info
        $systemInfo = $this->getSystemInfo();

        // Verify license locally
        $localVerification = $this->verifyLocalLicense($systemInfo);

        // Verify license remotely if required
        $remoteVerification = true;
        if (config('tekpart.license.verify_remotely')) {
            $remoteVerification = $this->verifyRemoteLicense($systemInfo);
        }

        $isValid = $localVerification && $remoteVerification;

        // Cache the result for one day
        Cache::put('license_valid', $isValid, now()->addDay());

        return $isValid;
    }

    /**
     * Verify license locally
     *
     * @param array $systemInfo
     * @return bool
     */
    protected function verifyLocalLicense($systemInfo)
    {
        // Check if license file exists
        $licensePath = storage_path('app/license/license.dat');
        if (!File::exists($licensePath)) {
            return false;
        }

        // Read license file
        $licenseData = File::get($licensePath);

        // Decrypt license
        $licenseData = $this->decryptLicense($licenseData);
        if (!$licenseData) {
            return false;
        }

        // Convert license data to PHP object
        $license = json_decode($licenseData, true);
        if (!$license) {
            return false;
        }

        // Verify license validity
        if (!isset($license['domain']) || !isset($license['expiration'])) {
            return false;
        }

        // Verify expiration date
        if (strtotime($license['expiration']) < time()) {
            return false;
        }

        // Verify domain
        $currentDomain = $systemInfo['domain'];
        if ($license['domain'] !== '*' && $license['domain'] !== $currentDomain) {
            return false;
        }

        return true;
    }

    /**
     * Verify license with remote server
     *
     * @param array $systemInfo
     * @return bool
     */
    protected function verifyRemoteLicense($systemInfo)
    {
        $response = $this->client->validate($this->licenseKey, $systemInfo);

        if ($response && isset($response['valid'])) {
            return $response['valid'] === true;
        }

        // If we can't connect to the server, fallback to local verification only
        return true;
    }

    /**
     * Get system information
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
     * Decrypt license data
     *
     * @param string $encryptedData
     * @return string|false
     */
    protected function decryptLicense($encryptedData)
    {
        try {
            // Use public key to verify signature
            $publicKey = config('tekpart.license.public_key');
            if (!$publicKey) {
                return false;
            }

            // Initialize RSA
            $rsa = RSA::loadPublicKey($publicKey);

            // Decrypt
            list($signature, $data) = explode('|', base64_decode($encryptedData), 2);

            // Verify signature
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
     * Generate a new license
     *
     * @param array $licenseData
     * @return string
     */
    public function generateLicense($licenseData)
    {
        // Ensure private key exists
        $privateKey = config('tekpart.license.private_key');
        if (!$privateKey) {
            throw new \Exception('Private key is missing');
        }

        // Convert license data to JSON
        $licenseJson = json_encode($licenseData);

        // Initialize RSA
        $rsa = RSA::loadPrivateKey($privateKey);

        // Sign data
        $signature = base64_encode($rsa->sign($licenseJson));

        // Combine signature with data and encrypt
        $encryptedData = base64_encode($signature . '|' . $licenseJson);

        return $encryptedData;
    }

    /**
     * Generate a new key pair for encryption
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
     * Decrypt license data (public interface)
     *
     * @param string $encryptedData
     * @return string|false
     */
    public function decrypt($encryptedData)
    {
        return $this->decryptLicense($encryptedData);
    }

    /**
     * Activate a license key
     *
     * @param string $licenseKey
     * @param array $data Additional data for activation
     * @return array|null
     */
    public function activate($licenseKey = null, array $data = [])
    {
        $key = $licenseKey ?? $this->licenseKey;
        $systemInfo = $this->getSystemInfo();

        // Merge additional data with system info
        $activationData = array_merge($systemInfo, $data);

        return $this->client->activate($key, $activationData);
    }

    /**
     * Deactivate a license key
     *
     * @param string $licenseKey
     * @return array|null
     */
    public function deactivate($licenseKey = null)
    {
        $key = $licenseKey ?? $this->licenseKey;
        $systemInfo = $this->getSystemInfo();

        return $this->client->deactivate($key, $systemInfo);
    }

    /**
     * Check license status
     *
     * @param string $licenseKey
     * @return array|null
     */
    public function check($licenseKey = null)
    {
        $key = $licenseKey ?? $this->licenseKey;

        return $this->client->check($key);
    }

    /**
     * Generate a license token
     *
     * @param array $data
     * @param string $licenseKey
     * @return array|null
     */
    public function generateToken(array $data = [], $licenseKey = null)
    {
        $key = $licenseKey ?? $this->licenseKey;

        return $this->client->generateToken($key, $data);
    }

    /**
     * Verify a license token
     *
     * @param string $token
     * @return array|null
     */
    public function verifyToken($token)
    {
        return $this->client->verifyToken($token);
    }

    /**
     * Validate a license key
     *
     * @param string $licenseKey
     * @param array $systemInfo
     * @return array|null
     */
    public function validate($licenseKey = null, array $systemInfo = [])
    {
        $key = $licenseKey ?? $this->licenseKey;

        if (empty($systemInfo)) {
            $systemInfo = $this->getSystemInfo();
        }

        return $this->client->validate($key, $systemInfo);
    }
}
