<?php

namespace App;

class Config
{
    private static ?array $env = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$env === null) {
            self::loadEnv();
        }

        return self::$env[$key] ?? $default;
    }

    private static function loadEnv(): void
    {
        self::$env = [];

        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                self::$env[trim($key)] = trim($value);
            }
        }
    }

    public static function dbPath(): string
    {
        return __DIR__ . '/../' . self::get('DB_PATH', 'database.sqlite');
    }

    public static function logPath(): string
    {
        return __DIR__ . '/../' . self::get('LOG_PATH', 'logs/');
    }

    public static function appUrl(): string
    {
        return rtrim(self::get('APP_URL', 'http://localhost'), '/');
    }
}
