<?php
/**
 * Test script for checking system configuration
 * Run: php test.php
 */

require_once __DIR__ . '/autoload.php';

use App\Config;
use App\Database;
use App\Logger;
use App\Services\TelegramService;

echo "=== System Configuration Test ===" . PHP_EOL . PHP_EOL;

// Test 1: PHP Version
echo "1. PHP Version: ";
$phpVersion = phpversion();
echo $phpVersion;
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo " ✅" . PHP_EOL;
} else {
    echo " ❌ (Required >= 8.0)" . PHP_EOL;
}

// Test 2: Required Extensions
echo PHP_EOL . "2. PHP Extensions:" . PHP_EOL;
$required = ['sqlite3', 'curl', 'json', 'mbstring'];
foreach ($required as $ext) {
    echo "   - {$ext}: ";
    if (extension_loaded($ext)) {
        echo "✅" . PHP_EOL;
    } else {
        echo "❌ (Missing)" . PHP_EOL;
    }
}

// Test 3: .env file
echo PHP_EOL . "3. Configuration:" . PHP_EOL;
echo "   - .env file: ";
if (file_exists(__DIR__ . '/.env')) {
    echo "✅" . PHP_EOL;
    
    // Test config values
    $appUrl = Config::appUrl();
    echo "   - APP_URL: {$appUrl}" . PHP_EOL;
} else {
    echo "❌ (Missing - copy from .env.example)" . PHP_EOL;
}

// Test 4: Database
echo PHP_EOL . "4. Database:" . PHP_EOL;
$dbPath = Config::dbPath();
echo "   - Path: {$dbPath}" . PHP_EOL;
echo "   - Exists: ";
if (file_exists($dbPath)) {
    echo "✅" . PHP_EOL;
    
    try {
        $pdo = Database::getConnection();
        
        // Check tables
        $tables = ['bots', 'channels', 'invites', 'event_logs'];
        echo "   - Tables:" . PHP_EOL;
        foreach ($tables as $table) {
            $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
            $exists = $result->fetch();
            echo "     * {$table}: " . ($exists ? "✅" : "❌") . PHP_EOL;
        }
        
        // Check counts
        $stats = Database::fetchOne("SELECT COUNT(*) as count FROM bots");
        echo "   - Bots count: " . $stats['count'] . PHP_EOL;
        
        $stats = Database::fetchOne("SELECT COUNT(*) as count FROM channels");
        echo "   - Channels count: " . $stats['count'] . PHP_EOL;
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . PHP_EOL;
    }
} else {
    echo "❌ (Run: php setup.php)" . PHP_EOL;
}

// Test 5: Logs directory
echo PHP_EOL . "5. Logs:" . PHP_EOL;
$logPath = Config::logPath();
echo "   - Path: {$logPath}" . PHP_EOL;
echo "   - Writable: ";
if (is_dir($logPath) && is_writable($logPath)) {
    echo "✅" . PHP_EOL;
} else {
    echo "❌ (Directory not writable)" . PHP_EOL;
}

// Test 6: Telegram API (optional)
echo PHP_EOL . "6. Telegram API Test:" . PHP_EOL;
echo "   Enter bot token to test (or press Enter to skip): ";
$handle = fopen("php://stdin", "r");
$token = trim(fgets($handle));
fclose($handle);

if (!empty($token)) {
    echo "   Testing token..." . PHP_EOL;
    
    try {
        $telegram = new TelegramService($token);
        $botInfo = $telegram->getMe();
        
        if ($botInfo) {
            echo "   ✅ Bot connected successfully!" . PHP_EOL;
            echo "   - Bot name: " . $botInfo['first_name'] . PHP_EOL;
            echo "   - Bot username: @" . $botInfo['username'] . PHP_EOL;
            
            // Check webhook
            $webhookInfo = $telegram->getWebhookInfo();
            if ($webhookInfo) {
                echo "   - Webhook URL: " . ($webhookInfo['url'] ?: '(not set)') . PHP_EOL;
                echo "   - Pending updates: " . ($webhookInfo['pending_update_count'] ?? 0) . PHP_EOL;
            }
        } else {
            echo "   ❌ Failed to connect to bot" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . PHP_EOL;
    }
} else {
    echo "   ⏭️  Skipped" . PHP_EOL;
}

// Test 7: Web server
echo PHP_EOL . "7. Web Server:" . PHP_EOL;
echo "   - Document root should be: " . __DIR__ . "/public" . PHP_EOL;
echo "   - Make sure HTTPS is configured for webhook to work" . PHP_EOL;

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
echo PHP_EOL . "Next steps:" . PHP_EOL;
echo "1. Configure web server (Nginx/Apache)" . PHP_EOL;
echo "2. Set up HTTPS (required for Telegram webhook)" . PHP_EOL;
echo "3. Open admin panel in browser" . PHP_EOL;
echo "4. Add your first bot and channel" . PHP_EOL;
