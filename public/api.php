<?php

require_once __DIR__ . '/../autoload.php';

use App\Models\Bot;
use App\Models\Channel;
use App\Models\Invite;
use App\Services\TelegramService;
use App\Config;
use App\Auth;

header('Content-Type: application/json');

// Check authentication
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = get('action');

try {
    switch ($action) {
        // ===== BOTS =====
        case 'get_bots':
            $bots = Bot::all();
            success_response(array_map(fn($bot) => $bot->toArray(), $bots));

        case 'add_bot':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $name = post('name');
            $token = post('token');
            
            $errors = validate_required(['name', 'token'], $_POST);
            if ($errors) error_response(implode(', ', $errors));
            
            // Test token
            if (!TelegramService::testToken($token)) {
                error_response('Invalid bot token');
            }
            
            $bot = Bot::create($name, $token);
            if (!$bot) error_response('Failed to create bot');
            
            success_response($bot->toArray(), 'Bot added successfully');

        case 'delete_bot':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            if (!$id) error_response('Missing bot ID');
            
            $bot = Bot::findById($id);
            if (!$bot) error_response('Bot not found');
            
            // Delete webhook
            $telegram = new TelegramService($bot->token);
            $telegram->deleteWebhook();
            
            $bot->delete();
            success_response([], 'Bot deleted successfully');

        case 'update_bot':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            $name = post('name');
            $token = post('token');
            
            $errors = validate_required(['id', 'name', 'token'], $_POST);
            if ($errors) error_response(implode(', ', $errors));
            
            $bot = Bot::findById($id);
            if (!$bot) error_response('Bot not found');
            
            // Validate new token
            if (!TelegramService::testToken($token)) {
                error_response('Invalid bot token');
            }
            
            $bot->name = $name;
            $bot->token = $token;
            $bot->update();
            
            success_response($bot->toArray(), 'Bot updated successfully');

        case 'toggle_bot':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            if (!$id) error_response('Missing bot ID');
            
            $bot = Bot::findById($id);
            if (!$bot) error_response('Bot not found');
            
            $bot->is_active = !$bot->is_active;
            $bot->update();
            
            success_response($bot->toArray(), 'Bot status updated');

        // ===== CHANNELS =====
        case 'get_channels':
            $botId = get('bot_id');
            
            if ($botId) {
                $channels = Channel::findByBotId($botId);
            } else {
                $channels = Channel::all();
            }
            
            success_response(array_map(fn($ch) => $ch->toArray(), $channels));

        case 'add_channel':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $botId = post('bot_id');
            $channelId = post('channel_id');
            $postbackUrl = post('postback_url');
            $channelName = post('channel_name');
            
            $errors = validate_required(['bot_id', 'channel_id', 'postback_url'], $_POST);
            if ($errors) error_response(implode(', ', $errors));
            
            // Get bot
            $bot = Bot::findById($botId);
            if (!$bot) error_response('Bot not found');
            
            // Validate bot permissions
            $telegram = new TelegramService($bot->token);
            $validationErrors = $telegram->validateBotPermissions($channelId);
            
            if ($validationErrors) {
                error_response('Bot validation failed: ' . implode(', ', $validationErrors));
            }
            
            // Get channel info
            $chatInfo = $telegram->getChat($channelId);
            $channelName = $channelName ?: ($chatInfo['title'] ?? null);
            
            // Create channel
            $channel = Channel::create($botId, $channelId, $postbackUrl, $channelName);
            if (!$channel) error_response('Failed to create channel');
            
            // Set webhook if not set
            $webhookUrl = Config::appUrl() . '/webhook.php?token=' . urlencode($bot->token);
            $telegram->setWebhook($webhookUrl);
            
            success_response($channel->toArray(), 'Channel added successfully');

        case 'delete_channel':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            if (!$id) error_response('Missing channel ID');
            
            $channel = Channel::findById($id);
            if (!$channel) error_response('Channel not found');
            
            $channel->delete();
            success_response([], 'Channel deleted successfully');

        case 'update_channel':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            $channelName = post('channel_name');
            $postbackUrl = post('postback_url');
            
            $errors = validate_required(['id', 'postback_url'], $_POST);
            if ($errors) error_response(implode(', ', $errors));
            
            $channel = Channel::findById($id);
            if (!$channel) error_response('Channel not found');
            
            $channel->channel_name = $channelName ?: $channel->channel_name;
            $channel->postback_url = $postbackUrl;
            $channel->update();
            
            success_response($channel->toArray(), 'Channel updated successfully');

        case 'toggle_channel':
            if (!is_post()) error_response('Method not allowed', 405);
            
            $id = post('id');
            if (!$id) error_response('Missing channel ID');
            
            $channel = Channel::findById($id);
            if (!$channel) error_response('Channel not found');
            
            $channel->is_active = !$channel->is_active;
            $channel->update();
            
            success_response($channel->toArray(), 'Channel status updated');

        // ===== INVITES =====
        case 'get_invites':
            $channelId = get('channel_id');
            $limit = get('limit', 100);
            
            if ($channelId) {
                $invites = Invite::findByChannelId($channelId, $limit);
            } else {
                error_response('channel_id is required');
            }
            
            success_response(array_map(fn($inv) => $inv->toArray(), $invites));

        case 'get_stats':
            $stats = Invite::getStats();
            success_response($stats);

        default:
            error_response('Unknown action', 400);
    }
} catch (Exception $e) {
    error_response('Internal error: ' . $e->getMessage(), 500);
}
