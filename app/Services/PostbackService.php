<?php

namespace App\Services;

use App\Models\Invite;
use App\Models\Channel;
use App\Logger;

class PostbackService
{
    /**
     * Send postback to tracker
     */
    public static function send(Invite $invite, string $status = 'lead', array $userData = []): bool
    {
        $channel = $invite->getChannel();
        if (!$channel) {
            Logger::error("Postback failed: Channel not found for invite {$invite->id}");
            return false;
        }

        // Build postback URL with macros
        $url = self::buildUrl($channel->postback_url, $invite->subid, $status, $userData);

        Logger::postback("Sending postback: {$url}");

        // Send request
        $success = self::sendRequest($url);

        if ($success) {
            Logger::postback("Postback sent successfully: subid={$invite->subid}, status={$status}");
        } else {
            Logger::error("Postback failed: subid={$invite->subid}, url={$url}");
        }

        // Log event
        self::logEvent($channel->id, $invite->subid, $url, $success);

        return $success;
    }

    /**
     * Build postback URL from template with macro replacement
     * 
     * Available macros:
     * {clickid} - Click ID from tracker
     * {status} - Status (lead, sale)
     * {user_id} - Telegram user ID
     * {first_name} - User first name
     * {last_name} - User last name
     * {username} - User @username
     * {is_premium} - Premium status (true/false)
     * {language_code} - User language (ru, en, etc.)
     */
    private static function buildUrl(string $template, string $subid, string $status, array $userData = []): string
    {
        // Prepare replacements
        $replacements = [
            '{clickid}' => urlencode($subid),
            '{subid}' => urlencode($subid), // Backward compatibility
            '{status}' => urlencode($status),
            '{user_id}' => urlencode($userData['user_id'] ?? ''),
            '{first_name}' => urlencode($userData['first_name'] ?? ''),
            '{last_name}' => urlencode($userData['last_name'] ?? ''),
            '{username}' => urlencode($userData['username'] ?? ''),
            '{is_premium}' => urlencode($userData['is_premium'] ?? 'false'),
            '{language_code}' => urlencode($userData['language_code'] ?? '')
        ];
        
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    /**
     * Send HTTP request
     */
    private static function sendRequest(string $url): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For development, set to true in production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development, set to true in production

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error("Postback request error: {$error}");
            return false;
        }

        // Consider 2xx and 3xx as success
        if ($httpCode >= 200 && $httpCode < 400) {
            return true;
        }

        Logger::error("Postback returned HTTP {$httpCode}: {$response}");
        return false;
    }

    /**
     * Log postback event to database
     */
    private static function logEvent(int $channelId, string $subid, string $url, bool $success): void
    {
        try {
            \App\Database::query(
                "INSERT INTO event_logs (event_type, channel_id, subid, details) VALUES (?, ?, ?, ?)",
                [
                    'postback_sent',
                    $channelId,
                    $subid,
                    json_encode([
                        'url' => $url,
                        'success' => $success,
                        'timestamp' => date('Y-m-d H:i:s')
                    ])
                ]
            );
        } catch (\Exception $e) {
            Logger::error("Failed to log postback event: " . $e->getMessage());
        }
    }
}
