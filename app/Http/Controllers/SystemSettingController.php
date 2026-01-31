<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    /**
     * ✅ FIX: Display system settings index
     */
    public function index()
    {
        // Get all settings grouped by category
        $settings = SystemSetting::orderBy('category')->orderBy('key')->get();

        $groupedSettings = $settings->groupBy('category');

        return view('admin.settings.index', [
            'groupedSettings' => $groupedSettings,
            'settings' => $settings,
        ]);
    }

    /**
     * Update multiple settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updatedCount = 0;
        $errors = [];

        // ✅ FIX: Checkbox yang tidak dicek tidak dikirim browser sama sekali.
        // Tambahkan 'false' secara manual untuk semua setting bertipe boolean
        // yang tidak ada di request.
        $allSettings = SystemSetting::all();
        foreach ($allSettings as $s) {
            if ($s->type === 'boolean' && !isset($request->settings[$s->key])) {
                $request->merge(['settings' => array_merge($request->settings, [$s->key => 'false'])]);
            }
        }

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();

            if (!$setting) {
                $errors[] = "Setting '{$key}' tidak ditemukan";
                continue;
            }

            // Validate based on type
            $validated = $this->validateSettingValue($value, $setting->type, $setting->key);

            if ($validated['error']) {
                $errors[] = $validated['error'];
                continue;
            }

            $setting->value = $validated['value'];
            $setting->updated_by = Auth::id();
            $setting->save();

            $updatedCount++;
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', 'Beberapa pengaturan gagal diupdate: ' . implode(', ', $errors))
                ->with('success', "{$updatedCount} pengaturan berhasil diupdate");
        }

        return redirect()->back()
            ->with('success', "{$updatedCount} pengaturan berhasil diupdate!");
    }

    /**
     * Validate setting value based on type
     */
    private function validateSettingValue($value, $type, $key)
    {
        switch ($type) {
            case 'integer':
                if (!is_numeric($value) || $value < 0) {
                    return [
                        'error' => "Nilai untuk '{$key}' harus berupa angka positif",
                        'value' => null
                    ];
                }
                return ['error' => null, 'value' => (string)(int)$value];

            case 'boolean':
                $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolValue === null && $value !== 'false' && $value !== '0') {
                    return [
                        'error' => "Nilai untuk '{$key}' harus berupa boolean (true/false)",
                        'value' => null
                    ];
                }
                return ['error' => null, 'value' => $boolValue ? 'true' : 'false'];

            case 'json':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [
                        'error' => "Nilai untuk '{$key}' harus berupa JSON yang valid",
                        'value' => null
                    ];
                }
                return ['error' => null, 'value' => $value];

            default:
                return ['error' => null, 'value' => (string)$value];
        }
    }

    /**
     * ✅ FIX: Reset settings to default
     */
    public function reset(Request $request)
    {
        $category = $request->input('category');

        if ($category) {
            // Reset specific category
            $settings = SystemSetting::where('category', $category)->get();
            $message = "Pengaturan kategori '{$category}' berhasil direset ke nilai default!";
        } else {
            // Reset all settings
            $settings = SystemSetting::all();
            $message = 'Semua pengaturan berhasil direset ke nilai default!';
        }

        // Re-seed default values
        $resetCount = 0;
        foreach ($settings as $setting) {
            $defaultValue = $this->getDefaultValue($setting->key);
            if ($defaultValue !== null) {
                $setting->value = $defaultValue;
                $setting->updated_by = Auth::id();
                $setting->save();
                $resetCount++;
            }
        }

        return redirect()->back()
            ->with('success', "{$message} ({$resetCount} pengaturan direset)");
    }

    /**
     * Get default value for a setting key
     */
    private function getDefaultValue($key)
    {
        $defaults = [
            'auto_reject_days' => '5',
            'revision_debug_mode' => 'false',
            'revision_debug_minutes' => '5',
            'max_revision_count' => '3',
            'enable_auto_reject' => 'true',
        ];

        return $defaults[$key] ?? null;
    }

    /**
     * ✅ FIX: Get current setting values (API for testing)
     */
    public function getCurrent()
    {
        return response()->json([
            'auto_reject_days' => SystemSetting::get('auto_reject_days', 5),
            'revision_debug_mode' => SystemSetting::get('revision_debug_mode', false),
            'revision_debug_minutes' => SystemSetting::get('revision_debug_minutes', 5),
            'max_revision_count' => SystemSetting::get('max_revision_count', 3),
            'enable_auto_reject' => SystemSetting::get('enable_auto_reject', true),
        ]);
    }
}
