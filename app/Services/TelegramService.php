<?php

namespace App\Services;

use App\Logger;

class TelegramService
{
    private string $token;
    private string $apiUrl = 'https://api.telegram.org/bot';

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Make request to Telegram Bot API
     */
    private function request(string $method, array $params = []): mixed
    {
        $url = $this->apiUrl . $this->token . '/' . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For development, set to true in production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development, set to true in production

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error("Telegram API request failed: {$error}");
            return null;
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['ok']) || !$data['ok']) {
            $errorMsg = $data['description'] ?? 'Unknown error';
            Logger::error("Telegram API error: {$errorMsg}");
            return null;
        }

        return $data['result'] ?? null;
    }

    /**
     * Get bot info
     */
    public function getMe(): ?array
    {
        $result = $this->request('getMe');
        return is_array($result) ? $result : null;
    }

    /**
     * Create chat invite link
     */
    public function createChatInviteLink(string $chatId, int $memberLimit = 1): ?string
    {
        $result = $this->request('createChatInviteLink', [
            'chat_id' => $chatId,
            'member_limit' => $memberLimit,
            'creates_join_request' => false,
        ]);

        return is_array($result) ? ($result['invite_link'] ?? null) : null;
    }

    /**
     * Get chat member info
     */
    public function getChatMember(string $chatId, int $userId): ?array
    {
        $result = $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
        return is_array($result) ? $result : null;
    }

    /**
     * Get chat info
     */
    public function getChat(string $chatId): ?array
    {
        $result = $this->request('getChat', [
            'chat_id' => $chatId,
        ]);
        return is_array($result) ? $result : null;
    }

    /**
     * Set webhook
     */
    public function setWebhook(string $url): bool
    {
        $result = $this->request('setWebhook', [
            'url' => $url,
            'allowed_updates' => json_encode(['chat_member']),
            'drop_pending_updates' => true,
        ]);

        return $result === true;
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): bool
    {
        $result = $this->request('deleteWebhook');
        return $result === true;
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): ?array
    {
        $result = $this->request('getWebhookInfo');
        return is_array($result) ? $result : null;
    }

    /**
     * Validate bot permissions in channel
     */
    public function validateBotPermissions(string $chatId): array
    {
        $errors = [];

        // Get bot info
        $botInfo = $this->getMe();
        if (!$botInfo) {
            $errors[] = 'Failed to get bot information';
            return $errors;
        }

        $botId = $botInfo['id'];

        // Get chat info
        $chatInfo = $this->getChat($chatId);
        if (!$chatInfo) {
            $errors[] = 'Failed to get chat information. Make sure the bot is added to the channel.';
            return $errors;
        }

        // Check if it's a channel
        if ($chatInfo['type'] !== 'channel') {
            $errors[] = 'Chat is not a channel (type: ' . $chatInfo['type'] . ')';
        }

        // Get bot member status
        $member = $this->getChatMember($chatId, $botId);
        if (!$member) {
            $errors[] = 'Bot is not a member of this channel';
            return $errors;
        }

        // Check if bot is admin
        if (!in_array($member['status'], ['administrator', 'creator'])) {
            $errors[] = 'Bot must be an administrator of the channel';
            return $errors;
        }

        // Check can_invite_users permission
        if (isset($member['can_invite_users']) && !$member['can_invite_users']) {
            $errors[] = 'Bot does not have "can_invite_users" permission';
        }

        return $errors;
    }

    /**
     * Test bot token validity
     */
    public static function testToken(string $token): bool
    {
        $service = new self($token);
        $result = $service->getMe();
        return is_array($result) && !empty($result);
    }
}
