<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::pluck('value', 'key');

        return response()->json(['settings' => $settings]);
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        return response()->json(['key' => $setting->key, 'value' => $setting->value]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    public function publicSettings(): JsonResponse
    {
        $publicKeys = [
            'site_name',
            'site_email',
            'site_phone',
            'site_address',
            'currency',
            'currency_symbol',
            'tax_rate',
            'free_shipping_threshold',
            'default_delivery_fee',
        ];

        $settings = Setting::whereIn('key', $publicKeys)->pluck('value', 'key');

        return response()->json(['settings' => $settings]);
    }
}
