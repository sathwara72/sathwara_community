<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    public const LOCALES = ['en', 'gu'];
    public const GROUPS = ['messages', 'json'];

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * Admin overrides for one locale + group as [key => value], cached until the next edit.
     */
    public static function overrides(string $locale, string $group): array
    {
        return Cache::rememberForever(self::cacheKey($locale, $group), function () use ($locale, $group) {
            return self::where('locale', $locale)->where('group', $group)->pluck('value', 'key')->all();
        });
    }

    public static function flushCache(): void
    {
        foreach (self::LOCALES as $locale) {
            foreach (self::GROUPS as $group) {
                Cache::forget(self::cacheKey($locale, $group));
            }
        }
    }

    protected static function cacheKey(string $locale, string $group): string
    {
        return "translations.overrides.{$locale}.{$group}";
    }
}
