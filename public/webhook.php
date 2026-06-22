<?php

require_once __DIR__ . '/../autoload.php';

use App\Models\Bot;
use App\Models\Invite;
use App\Services\PostbackService;
use App\Logger;

// Get bot token from query parameter
$token = get('token');

if (!$token) {
    error_response('Missing token parameter', 400);
}

// Get request body
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Log incoming webhook
Logger::webhook("Received webhook for token: " . substr($token, 0, 10) . "...");
Logger::webhook("Update: " . $input);

if (!$update) {
    error_response('Invalid JSON', 400);
}

try {
    // Find bot by token
    $bot = Bot::findByToken($token);
    if (!$bot || !$bot->is_active) {
        Logger::error("Bot not found or inactive for token: " . substr($token, 0, 10));
        error_response('Bot not found', 404);
    }

    // Process chat_member update
    if (isset($update['chat_member'])) {
        $chatMember = $update['chat_member'];
        
        $chatId = $chatMember['chat']['id'] ?? null;
        $newStatus = $chatMember['new_chat_member']['status'] ?? null;
        $userId = $chatMember['new_chat_member']['user']['id'] ?? null;
        $inviteLink = $chatMember['invite_link']['invite_link'] ?? null;

        Logger::webhook("Chat member update: chat={$chatId}, status={$newStatus}, user={$userId}");

        // Check if user joined (became member)
        if ($newStatus === 'member' && $inviteLink && $userId) {
            Logger::info("User {$userId} joined via invite: {$inviteLink}");

            // Extract user data for postback macros
            $user = $chatMember['new_chat_member']['user'] ?? [];
            $userData = [
                'user_id' => $userId,
                'first_name' => $user['first_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'username' => $user['username'] ?? '',
                'is_premium' => isset($user['is_premium']) && $user['is_premium'] ? 'true' : 'false',
                'language_code' => $user['language_code'] ?? ''
            ];

            // Find invite by link
            $invite = Invite::findByInviteLink($inviteLink);

            if (!$invite) {
                Logger::error("Invite not found for link: {$inviteLink}");
            } elseif ($invite->status !== 'pending') {
                Logger::info("Invite already processed: subid={$invite->subid}, status={$invite->status}");
            } else {
                // Mark as subscribed
                $invite->markAsSubscribed($userId);

                // Send postback with user data
                PostbackService::send($invite, 'lead', $userData);

                Logger::info("Subscription processed successfully: subid={$invite->subid}");

                // Log event
                \App\Database::query(
                    "INSERT INTO event_logs (event_type, channel_id, subid, details) VALUES (?, ?, ?, ?)",
                    [
                        'subscription',
                        $invite->channel_id,
                        $invite->subid,
                        json_encode(array_merge([
                            'invite_link' => $inviteLink
                        ], $userData))
                    ]
                );
            }
        } elseif ($newStatus === 'left' || $newStatus === 'kicked') {
            Logger::info("User {$userId} left/kicked from chat {$chatId}");
        }
    }

    // Return OK to Telegram
    http_response_code(200);
    echo 'OK';

} catch (Exception $e) {
    Logger::error("Webhook processing error: " . $e->getMessage());
    http_response_code(500);
    echo 'ERROR';
}
