<?php

namespace App;

class Logger
{
    private static function log(string $level, string $message, string $file = 'app.log'): void
    {
        $logPath = Config::logPath();
        
        // Create logs directory if not exists
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents($logPath . $file, $logMessage, FILE_APPEND);
    }

    public static function info(string $message, string $file = 'app.log'): void
    {
        self::log('INFO', $message, $file);
    }

    public static function error(string $message, string $file = 'errors.log'): void
    {
        self::log('ERROR', $message, $file);
    }

    public static function debug(string $message, string $file = 'app.log'): void
    {
        self::log('DEBUG', $message, $file);
    }

    public static function webhook(string $message): void
    {
        self::log('WEBHOOK', $message, 'webhook.log');
    }

    public static function postback(string $message): void
    {
        self::log('POSTBACK', $message, 'postback.log');
    }
}
