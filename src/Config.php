<?php

declare(strict_types=1);

namespace App;

final class Config
{
    private static ?array $config = null;

    public static function all(): array
    {
        if (self::$config === null) {
            self::$config = require dirname(__DIR__) . '/config/config.php';
        }

        return self::$config;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $value = self::all();

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
