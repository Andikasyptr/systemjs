<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    // Nilai yang tersimpan di database (di-cache)
    public static function stored(): array
    {
        return Cache::rememberForever('settings.stored', fn () => static::pluck('value', 'key')->all());
    }

    // Gabungan nilai database + default dari config/site.php
    public static function forSite(): array
    {
        $stored = static::stored();
        $out = [];

        foreach (config('site.groups') as $fields) {
            foreach ($fields as $key => [$label, $type, $default]) {
                $out[$key] = $stored[$key] ?? $default;
            }
        }

        return $out;
    }

    public static function put(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings.stored');
    }
}