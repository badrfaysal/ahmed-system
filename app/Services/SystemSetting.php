<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value store للإعدادات اللي بتتغير عبر الوقت.
 * - بتقرا من جدول system_settings مع caching في الذاكرة لتفادي queries متكررة.
 * - بتحوّل القيمة لنوعها الصحيح تلقائي (number / boolean / json / string).
 *
 * الاستخدام:
 *   SystemSetting::get('fuel_profit_rate', 0.01);
 *   SystemSetting::set('company_name', 'شركة جديدة');
 *   SystemSetting::group('fuel'); // كل إعدادات قسم البنزينة
 */
class SystemSetting
{
    private static ?array $cache = null;
    private static ?array $meta  = null;

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) self::loadCache();
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        if (!Schema::hasTable('system_settings')) return;

        $stored = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $stored, 'updated_at' => now()]
        );
        self::clearCache();
    }

    public static function all(): array
    {
        if (self::$cache === null) self::loadCache();
        return self::$cache;
    }

    /**
     * Returns full rows (key, value casted, type, label, description, group) for a group.
     */
    public static function group(string $name): array
    {
        if (self::$meta === null) self::loadCache();
        return array_values(array_filter(self::$meta, fn($r) => ($r['group'] ?? null) === $name));
    }

    /**
     * All groups with their rows — useful for the settings UI.
     */
    public static function grouped(): array
    {
        if (self::$meta === null) self::loadCache();
        $out = [];
        foreach (self::$meta as $row) {
            $out[$row['group']][] = $row;
        }
        return $out;
    }

    public static function forget(string $key): void
    {
        if (!Schema::hasTable('system_settings')) return;
        DB::table('system_settings')->where('key', $key)->where('user_added', true)->delete();
        self::clearCache();
    }

    public static function clearCache(): void
    {
        self::$cache = null;
        self::$meta  = null;
    }

    private static function loadCache(): void
    {
        self::$cache = [];
        self::$meta  = [];

        if (!Schema::hasTable('system_settings')) return;

        $rows = DB::table('system_settings')->orderBy('group')->orderBy('id')->get();
        foreach ($rows as $r) {
            self::$cache[$r->key] = self::castValue($r->value, $r->type);
            self::$meta[$r->key] = [
                'key'         => $r->key,
                'value'       => self::$cache[$r->key],
                'raw'         => $r->value,
                'type'        => $r->type,
                'group'       => $r->group,
                'label'       => $r->label,
                'description' => $r->description,
                'user_added'  => (bool) $r->user_added,
            ];
        }
    }

    private static function castValue($value, string $type)
    {
        if ($value === null) return null;
        return match ($type) {
            'number'  => is_numeric($value) ? (float) $value : 0,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true) ?? [],
            default   => (string) $value,
        };
    }
}
