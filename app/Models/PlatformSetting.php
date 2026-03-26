<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Retrieve a platform setting value by key, casting by its stored type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Upsert a platform setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        $serialized = is_array($value) || is_object($value)
            ? json_encode($value)
            : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $serialized],
        );
    }
}
