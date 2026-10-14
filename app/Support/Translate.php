<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

/**
 * Typed access to translations for PHP code (Laravel's __() can also return arrays).
 * Missing keys follow the language fallback chain configured in AppServiceProvider.
 */
class Translate
{
    /**
     * @param  array<string, string|int|float>  $replace
     */
    public static function text(string $key, array $replace = [], ?string $locale = null): string
    {
        $value = Lang::get($key, $replace, $locale);

        return is_string($value) ? $value : $key;
    }

    /**
     * @return list<string>
     */
    public static function list(string $key, ?string $locale = null): array
    {
        $value = Lang::get($key, [], $locale);

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function map(string $key, ?string $locale = null): array
    {
        $value = Lang::get($key, [], $locale);

        if (! is_array($value)) {
            return [];
        }

        $map = [];
        foreach ($value as $k => $v) {
            $map[(string) $k] = $v;
        }

        return $map;
    }

    /**
     * Whether the language has its own text for the key (no fallback to another language).
     */
    public static function hasOwn(string $key, string $locale): bool
    {
        return Lang::hasForLocale($key, $locale);
    }
}
