<?php

namespace TekPart\License\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseClient
{
    /**
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * @var array
     */
    protected $defaultHeaders;

    /**
     * Create a new LicenseClient instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiBaseUrl = rtrim(config('tekpart.license.verification_server', 'https://license.tek-part.com'), '/');
        $this->defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Validate a license key
     *
     * @param string $licenseKey
     * @param array $systemInfo
     * @return array|null
     */
    public function validate($licenseKey, array $systemInfo = [])
    {
        return $this->makeRequest('POST', '/api/license/validate', [
            'license_key' => $licenseKey,
            'system_info' => $systemInfo,
        ]);
    }

    /**
     * Activate a license key
     *
     * @param string $licenseKey
     * @param array $systemInfo
     * @return array|null
     */
    public function activate($licenseKey, array $systemInfo = [])
    {
        return $this->makeRequest('POST', '/api/license/activate', [
            'license_key' => $licenseKey,
            'system_info' => $systemInfo,
        ]);
    }

    /**
     * Deactivate a license key
     *
     * @param string $licenseKey
     * @param array $systemInfo
     * @return array|null
     */
    public function deactivate($licenseKey, array $systemInfo = [])
    {
        return $this->makeRequest('POST', '/api/license/deactivate', [
            'license_key' => $licenseKey,
            'system_info' => $systemInfo,
        ]);
    }

    /**
     * Check license status
     *
     * @param string $licenseKey
     * @return array|null
     */
    public function check($licenseKey)
    {
        return $this->makeRequest('POST', '/api/license/check', [
            'license_key' => $licenseKey,
        ]);
    }

    /**
     * Generate a license token
     *
     * @param string $licenseKey
     * @param array $data
     * @return array|null
     */
    public function generateToken($licenseKey, array $data = [])
    {
        return $this->makeRequest('POST', '/api/license/token/generate', [
            'license_key' => $licenseKey,
            'data' => $data,
        ]);
    }

    /**
     * Verify a license token
     *
     * @param string $token
     * @return array|null
     */
    public function verifyToken($token)
    {
        return $this->makeRequest('POST', '/api/license/token/verify', [
            'token' => $token,
        ]);
    }

    /**
     * Make an HTTP request to the license API
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    protected function makeRequest($method, $endpoint, array $data = [])
    {
        try {
            $url = $this->apiBaseUrl . $endpoint;

            $response = Http::withHeaders($this->defaultHeaders)
                ->timeout(30)
                ->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("License API request failed: {$endpoint}", [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("License API exception: {$endpoint}", [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
