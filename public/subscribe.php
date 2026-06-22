<?php

require_once __DIR__ . '/../autoload.php';

use App\Models\Channel;
use App\Models\Invite;
use App\Services\TelegramService;
use App\Logger;

// Get parameters
$channelId = get('channel');
$clickid = get('clickid');

if (!$channelId || !$clickid) {
    error_response('Missing required parameters: channel and clickid', 400);
}

Logger::info("Subscribe request: channel={$channelId}, clickid={$clickid}");

try {
    // Find channel
    $channel = Channel::findByChannelId($channelId);
    if (!$channel || !$channel->is_active) {
        Logger::error("Channel not found or inactive: {$channelId}");
        error_response('Channel not found or inactive', 404);
    }

    // Check if invite already exists for this clickid
    $existingInvite = Invite::findBySubid($clickid);
    if ($existingInvite) {
        // Redirect to existing invite link
        Logger::info("Using existing invite for clickid={$clickid}");
        redirect($existingInvite->invite_link);
    }

    // Get bot
    $bot = $channel->getBot();
    if (!$bot || !$bot->is_active) {
        Logger::error("Bot not found or inactive for channel {$channelId}");
        error_response('Bot not found or inactive', 500);
    }

    // Create Telegram service
    $telegram = new TelegramService($bot->token);

    // Create invite link
    $inviteLink = $telegram->createChatInviteLink($channel->channel_id, 1);
    
    if (!$inviteLink) {
        Logger::error("Failed to create invite link for channel {$channelId}");
        error_response('Failed to create invite link. Check bot permissions.', 500);
    }

    // Save invite to database
    $invite = Invite::create($channel->id, $clickid, $inviteLink);
    
    if (!$invite) {
        Logger::error("Failed to save invite to database");
        error_response('Failed to save invite', 500);
    }

    Logger::info("Invite created successfully: clickid={$clickid}, link={$inviteLink}");

    // Log event
    \App\Database::query(
        "INSERT INTO event_logs (event_type, channel_id, subid, details) VALUES (?, ?, ?, ?)",
        [
            'invite_created',
            $channel->id,
            $clickid,
            json_encode(['invite_link' => $inviteLink])
        ]
    );

    // Redirect to Telegram invite link
    redirect($inviteLink);

} catch (Exception $e) {
    Logger::error("Subscribe error: " . $e->getMessage());
    error_response('Internal server error', 500);
}
