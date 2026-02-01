<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'label',
        'description',
        'is_public',
        'updated_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return self::all()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        }

        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set($key, $value, $updatedBy = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->updated_by = $updatedBy ?? Auth::id();
        $setting->save();

        Cache::forget("setting_{$key}");

        return true;
    }

    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    public static function getByCategory($category)
    {
        return Cache::remember("settings_category_{$category}", 3600, function () use ($category) {
            return self::where('category', $category)->get()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        });
    }

    public static function getAllCategories()
    {
        return Cache::remember('settings_all_categories', 3600, function () {
            return self::distinct('category')
                ->whereNotNull('category')
                ->pluck('category');
        });
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget("settings_category_{$setting->category}");
            Cache::forget('settings_all_categories');
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget("settings_category_{$setting->category}");
            Cache::forget('settings_all_categories');
        });
    }
}
