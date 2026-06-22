<?php

require_once __DIR__ . '/autoload.php';

use App\Config;
use App\Database;
use App\Logger;

echo "=== Telegram Channel Postback System Setup ===" . PHP_EOL . PHP_EOL;

// Check if .env exists
if (!file_exists(__DIR__ . '/.env')) {
    echo "⚠️  .env file not found. Creating from .env.example..." . PHP_EOL;
    if (file_exists(__DIR__ . '/.env.example')) {
        copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
        echo "✅ .env file created. Please edit it with your settings." . PHP_EOL;
    } else {
        die("❌ Error: .env.example not found!" . PHP_EOL);
    }
}

echo "📊 Creating database..." . PHP_EOL;

$dbPath = Config::dbPath();
$dbExists = file_exists($dbPath);

if ($dbExists) {
    echo "⚠️  Database already exists at: {$dbPath}" . PHP_EOL;
    echo "Do you want to recreate it? This will DELETE all data! (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'yes') {
        die("Setup cancelled." . PHP_EOL);
    }
    
    unlink($dbPath);
    echo "🗑️  Old database deleted." . PHP_EOL;
}

try {
    $pdo = Database::getConnection();
    
    // Create bots table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bots (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            token TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active INTEGER DEFAULT 1
        )
    ");
    
    // Create channels table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS channels (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            bot_id INTEGER NOT NULL,
            channel_id TEXT NOT NULL,
            channel_name TEXT,
            postback_url TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (bot_id) REFERENCES bots(id) ON DELETE CASCADE,
            UNIQUE(bot_id, channel_id)
        )
    ");
    
    // Create invites table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS invites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            channel_id INTEGER NOT NULL,
            subid TEXT UNIQUE NOT NULL,
            invite_link TEXT NOT NULL,
            status TEXT DEFAULT 'pending',
            user_telegram_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            subscribed_at DATETIME,
            FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE
        )
    ");
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_invites_subid ON invites(subid)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_invites_status ON invites(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_invites_link ON invites(invite_link)");
    
    // Create event_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_type TEXT NOT NULL,
            channel_id INTEGER,
            subid TEXT,
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ Database tables created successfully!" . PHP_EOL;
    
    // Create logs directory
    $logPath = Config::logPath();
    if (!is_dir($logPath)) {
        mkdir($logPath, 0755, true);
        echo "✅ Logs directory created: {$logPath}" . PHP_EOL;
    }
    
    Logger::info('Database setup completed successfully');
    
    echo PHP_EOL . "🎉 Setup completed!" . PHP_EOL;
    echo PHP_EOL . "Next steps:" . PHP_EOL;
    echo "1. Edit .env file with your settings" . PHP_EOL;
    echo "2. Configure your web server (document root = public/)" . PHP_EOL;
    echo "3. Open admin panel in browser" . PHP_EOL;
    echo "4. Add your Telegram bots and channels" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    Logger::error('Setup failed: ' . $e->getMessage());
    exit(1);
}
