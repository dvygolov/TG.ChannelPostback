<?php

namespace App;

class Auth
{
    private static string $sessionFile = '';
    private static string $attemptsFile = '';

    private static function init(): void
    {
        if (empty(self::$sessionFile)) {
            $logsPath = Config::logPath();
            self::$sessionFile = $logsPath . '.session';
            self::$attemptsFile = $logsPath . '.login_attempts';
            
            // Create logs directory if not exists
            if (!is_dir($logsPath)) {
                mkdir($logsPath, 0755, true);
            }
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        self::init();
        session_start();
        
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
            // Check if session is expired (24 hours)
            if (isset($_SESSION['expires_at']) && time() < $_SESSION['expires_at']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Authenticate user
     */
    public static function login(string $password): array
    {
        self::init();
        
        $ip = self::getClientIP();
        
        // Check rate limiting
        $lockout = self::checkRateLimit($ip);
        if ($lockout > 0) {
            return [
                'success' => false,
                'error' => "Слишком много попыток. Подождите {$lockout} секунд."
            ];
        }
        
        // Verify password
        $correctPassword = Config::get('ADMIN_PASSWORD', 'admin');
        
        if ($password === $correctPassword) {
            // Successful login
            session_start();
            $_SESSION['authenticated'] = true;
            $_SESSION['expires_at'] = time() + (24 * 3600); // 24 hours
            $_SESSION['ip'] = $ip;
            
            // Clear failed attempts
            self::clearAttempts($ip);
            
            Logger::info("Successful login from IP: {$ip}");
            
            return ['success' => true];
        } else {
            // Failed login
            self::recordFailedAttempt($ip);
            
            $attempts = self::getAttempts($ip);
            Logger::error("Failed login attempt from IP: {$ip} (attempt #{$attempts})");
            
            return [
                'success' => false,
                'error' => __("error_wrong_password")
            ];
        }
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        session_start();
        session_destroy();
    }

    /**
     * Get client IP address
     */
    private static function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    /**
     * Check rate limiting
     * Returns seconds to wait if locked out, 0 if not locked
     */
    private static function checkRateLimit(string $ip): int
    {
        $attempts = self::getAttempts($ip);
        
        if ($attempts >= 7) {
            // 7+ attempts: 1 hour lockout
            $lockTime = 3600;
        } elseif ($attempts >= 5) {
            // 5-6 attempts: 15 minutes lockout
            $lockTime = 900;
        } elseif ($attempts >= 3) {
            // 3-4 attempts: 5 minutes lockout
            $lockTime = 300;
        } else {
            // Less than 3 attempts: no lockout
            return 0;
        }
        
        $data = self::getAttemptsData();
        if (isset($data[$ip])) {
            $lastAttempt = $data[$ip]['last_attempt'];
            $elapsed = time() - $lastAttempt;
            
            if ($elapsed < $lockTime) {
                return $lockTime - $elapsed;
            }
        }
        
        return 0;
    }

    /**
     * Record failed login attempt
     */
    private static function recordFailedAttempt(string $ip): void
    {
        $data = self::getAttemptsData();
        
        if (!isset($data[$ip])) {
            $data[$ip] = [
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        $data[$ip]['count']++;
        $data[$ip]['last_attempt'] = time();
        
        // Clean old entries (older than 1 hour)
        foreach ($data as $key => $value) {
            if (time() - $value['last_attempt'] > 3600) {
                unset($data[$key]);
            }
        }
        
        file_put_contents(self::$attemptsFile, json_encode($data));
    }

    /**
     * Get number of failed attempts for IP
     */
    private static function getAttempts(string $ip): int
    {
        $data = self::getAttemptsData();
        return $data[$ip]['count'] ?? 0;
    }

    /**
     * Clear failed attempts for IP
     */
    private static function clearAttempts(string $ip): void
    {
        $data = self::getAttemptsData();
        unset($data[$ip]);
        file_put_contents(self::$attemptsFile, json_encode($data));
    }

    /**
     * Get all attempts data
     */
    private static function getAttemptsData(): array
    {
        if (!file_exists(self::$attemptsFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$attemptsFile);
        $data = json_decode($content, true);
        
        return $data ?: [];
    }
}
