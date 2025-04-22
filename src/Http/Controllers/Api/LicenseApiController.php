<?php

namespace TekPart\License\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TekPart\License\Facades\License;

class LicenseApiController extends Controller
{
    /**
     * Validate a license key
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'system_info' => 'nullable|array',
        ]);

        $result = License::validate($request->license_key, $request->system_info ?? []);

        if (!$result) {
            return response()->json([
                'valid' => false,
                'message' => 'Failed to validate license',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Activate a license key
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'system_info' => 'nullable|array',
        ]);

        $result = License::activate($request->license_key, $request->system_info ?? []);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate license',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Deactivate a license key
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'system_info' => 'nullable|array',
        ]);

        $result = License::deactivate($request->license_key);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate license',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Check license status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $result = License::check($request->license_key);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check license',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Generate a license token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $result = License::generateToken($request->data ?? [], $request->license_key);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Verify a license token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $result = License::verifyToken($request->token);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify token',
            ], 400);
        }

        return response()->json($result);
    }
}
