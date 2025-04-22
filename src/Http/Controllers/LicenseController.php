<?php

namespace TekPart\License\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use TekPart\License\Facades\License;

class LicenseController extends Controller
{
    /**
     * عرض حالة الترخيص الحالي.
     *
     * @return \Illuminate\View\View
     */
    public function status()
    {
        $isValid = License::verifyLicense();
        $licenseData = $this->getLicenseData();

        return view('teklicense::status', [
            'isValid' => $isValid,
            'licenseData' => $licenseData,
        ]);
    }

    /**
     * التحقق من الترخيص.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $isValid = License::verifyLicense();

        if ($request->expectsJson()) {
            return response()->json([
                'valid' => $isValid,
                'message' => $isValid ? 'الترخيص صالح' : 'الترخيص غير صالح أو منتهي الصلاحية',
            ]);
        }

        return redirect()->route('license.status')
            ->with('message', $isValid ? 'الترخيص صالح' : 'الترخيص غير صالح أو منتهي الصلاحية')
            ->with('status', $isValid ? 'success' : 'error');
    }

    /**
     * عرض صفحة الترخيص غير الصالح.
     *
     * @return \Illuminate\View\View
     */
    public function invalid()
    {
        return view('teklicense::invalid');
    }

    /**
     * عرض نموذج تفعيل الترخيص.
     *
     * @return \Illuminate\View\View
     */
    public function activateForm()
    {
        return view('teklicense::activate');
    }

    /**
     * تفعيل الترخيص.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'license_file' => 'nullable|file',
        ]);

        // إذا تم تحميل ملف ترخيص
        if ($request->hasFile('license_file')) {
            $file = $request->file('license_file');
            $licensePath = storage_path('app/license');

            // التأكد من وجود المجلد
            if (!File::exists($licensePath)) {
                File::makeDirectory($licensePath, 0755, true);
            }

            // حفظ ملف الترخيص
            $file->move($licensePath, 'license.dat');

            // تحديث مفتاح الترخيص في الإعدادات
            $this->updateEnvFile('TEKPART_LICENSE_KEY', $request->license_key);

            return redirect()->route('license.status')
                ->with('message', 'تم تفعيل الترخيص بنجاح')
                ->with('status', 'success');
        }

        return redirect()->route('license.activate.form')
            ->with('message', 'يرجى تحميل ملف الترخيص')
            ->with('status', 'error');
    }

    /**
     * الحصول على بيانات الترخيص الحالي.
     *
     * @return array|null
     */
    protected function getLicenseData()
    {
        $licensePath = storage_path('app/license/license.dat');
        if (!File::exists($licensePath)) {
            return null;
        }

        try {
            $licenseData = File::get($licensePath);
            $decryptedData = License::decryptLicense($licenseData);

            if ($decryptedData) {
                return json_decode($decryptedData, true);
            }
        } catch (\Exception $e) {
            \Log::error('Error reading license data: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * تحديث ملف .env
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $content = File::get($path);

            // التحقق من وجود المفتاح
            if (strpos($content, $key . '=') !== false) {
                // تحديث قيمة موجودة
                $content = preg_replace("/{$key}=.*/", "{$key}=\"{$value}\"", $content);
            } else {
                // إضافة قيمة جديدة
                $content .= "\n{$key}=\"{$value}\"";
            }

            File::put($path, $content);
        }
    }
}
