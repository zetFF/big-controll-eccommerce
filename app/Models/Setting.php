<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
        'is_public',
        'options'
    ];

    protected $casts = [
        'value' => 'json',
        'options' => 'json',
        'is_public' => 'boolean'
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $setting->update(['value' => $value]);
        return true;
    }

    public function getOptionsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }
} 