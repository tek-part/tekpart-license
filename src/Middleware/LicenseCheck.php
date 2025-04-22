<?php

namespace TekPart\License\Middleware;

use Closure;
use Illuminate\Http\Request;
use TekPart\License\Facades\License;

class LicenseCheck
{
    /**
     * معالجة الطلب.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // التحقق من صلاحية الترخيص
        if (!License::verifyLicense()) {
            // إذا كان طلب AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'الترخيص غير صالح أو منتهي الصلاحية',
                    'code' => 'invalid_license'
                ], 403);
            }

            // إذا كان تم تفعيل وضع الصيانة للتراخيص غير الصالحة
            if (config('tekpart.license.maintenance_mode_on_invalid', true)) {
                // تحويل إلى صفحة الترخيص
                return redirect()->route('license.invalid');
            }
        }

        return $next($request);
    }
}
