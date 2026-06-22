<?php

namespace App\Models;

use App\Database;
use App\Logger;

class Channel
{
    public ?int $id = null;
    public int $bot_id;
    public string $channel_id;
    public ?string $channel_name = null;
    public string $postback_url;
    public bool $is_active = true;
    public string $created_at;

    public static function create(
        int $bot_id,
        string $channel_id,
        string $postback_url,
        ?string $channel_name = null
    ): ?self {
        try {
            Database::query(
                "INSERT INTO channels (bot_id, channel_id, channel_name, postback_url) VALUES (?, ?, ?, ?)",
                [$bot_id, $channel_id, $channel_name, $postback_url]
            );
            
            $id = Database::lastInsertId();
            Logger::info("Channel created: {$channel_id} (ID: {$id})");
            
            return self::findById((int)$id);
        } catch (\PDOException $e) {
            Logger::error("Failed to create channel: " . $e->getMessage());
            return null;
        }
    }

    public static function findById(int $id): ?self
    {
        $row = Database::fetchOne("SELECT * FROM channels WHERE id = ?", [$id]);
        return $row ? self::fromArray($row) : null;
    }

    public static function findByBotAndChannelId(int $bot_id, string $channel_id): ?self
    {
        $row = Database::fetchOne(
            "SELECT * FROM channels WHERE bot_id = ? AND channel_id = ?",
            [$bot_id, $channel_id]
        );
        return $row ? self::fromArray($row) : null;
    }

    public static function findByChannelId(string $channel_id): ?self
    {
        $row = Database::fetchOne(
            "SELECT * FROM channels WHERE channel_id = ? AND is_active = 1 LIMIT 1",
            [$channel_id]
        );
        return $row ? self::fromArray($row) : null;
    }

    public static function findByBotId(int $bot_id): array
    {
        $rows = Database::fetchAll(
            "SELECT * FROM channels WHERE bot_id = ? ORDER BY created_at DESC",
            [$bot_id]
        );
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    public static function all(): array
    {
        $rows = Database::fetchAll("SELECT * FROM channels ORDER BY created_at DESC");
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    public function update(): bool
    {
        try {
            Database::query(
                "UPDATE channels SET channel_name = ?, postback_url = ?, is_active = ? WHERE id = ?",
                [$this->channel_name, $this->postback_url, $this->is_active ? 1 : 0, $this->id]
            );
            Logger::info("Channel updated: {$this->channel_id} (ID: {$this->id})");
            return true;
        } catch (\PDOException $e) {
            Logger::error("Failed to update channel: " . $e->getMessage());
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            Database::query("DELETE FROM channels WHERE id = ?", [$this->id]);
            Logger::info("Channel deleted: {$this->channel_id} (ID: {$this->id})");
            return true;
        } catch (\PDOException $e) {
            Logger::error("Failed to delete channel: " . $e->getMessage());
            return false;
        }
    }

    public function getBot(): ?Bot
    {
        return Bot::findById($this->bot_id);
    }

    private static function fromArray(array $data): self
    {
        $channel = new self();
        $channel->id = (int)$data['id'];
        $channel->bot_id = (int)$data['bot_id'];
        $channel->channel_id = $data['channel_id'];
        $channel->channel_name = $data['channel_name'];
        $channel->postback_url = $data['postback_url'];
        $channel->is_active = (bool)$data['is_active'];
        $channel->created_at = $data['created_at'];
        return $channel;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bot_id' => $this->bot_id,
            'channel_id' => $this->channel_id,
            'channel_name' => $this->channel_name,
            'postback_url' => $this->postback_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
