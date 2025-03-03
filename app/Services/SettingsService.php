<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    public function all()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()->groupBy('group');
        });
    }

    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, $value)
    {
        $result = Setting::set($key, $value);
        if ($result) {
            Cache::forget(self::CACHE_KEY);
        }
        return $result;
    }

    public function updateMany(array $settings)
    {
        $success = true;

        foreach ($settings as $key => $value) {
            if (!Setting::set($key, $value)) {
                $success = false;
            }
        }

        if ($success) {
            Cache::forget(self::CACHE_KEY);
        }

        return $success;
    }

    public function getPublicSettings()
    {
        return Cache::remember('public_settings', self::CACHE_TTL, function () {
            return Setting::where('is_public', true)
                ->get()
                ->pluck('value', 'key');
        });
    }

    public function export()
    {
        $settings = Setting::all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'group' => $setting->group,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
                'options' => $setting->options
            ];
        })->toArray();

        return json_encode($settings, JSON_PRETTY_PRINT);
    }

    public function import(string $json)
    {
        $settings = json_decode($json, true);
        if (!$settings) {
            return false;
        }

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        Cache::forget(self::CACHE_KEY);
        return true;
    }
} 