<?php

namespace App\Models;

use App\Database;
use App\Logger;

class Bot
{
    public ?int $id = null;
    public string $name;
    public string $token;
    public string $created_at;
    public bool $is_active = true;

    public static function create(string $name, string $token): ?self
    {
        try {
            Database::query(
                "INSERT INTO bots (name, token) VALUES (?, ?)",
                [$name, $token]
            );
            
            $id = Database::lastInsertId();
            Logger::info("Bot created: {$name} (ID: {$id})");
            
            return self::findById((int)$id);
        } catch (\PDOException $e) {
            Logger::error("Failed to create bot: " . $e->getMessage());
            return null;
        }
    }

    public static function findById(int $id): ?self
    {
        $row = Database::fetchOne("SELECT * FROM bots WHERE id = ?", [$id]);
        return $row ? self::fromArray($row) : null;
    }

    public static function findByToken(string $token): ?self
    {
        $row = Database::fetchOne("SELECT * FROM bots WHERE token = ?", [$token]);
        return $row ? self::fromArray($row) : null;
    }

    public static function all(): array
    {
        $rows = Database::fetchAll("SELECT * FROM bots ORDER BY created_at DESC");
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    public static function allActive(): array
    {
        $rows = Database::fetchAll("SELECT * FROM bots WHERE is_active = 1 ORDER BY created_at DESC");
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    public function update(): bool
    {
        try {
            Database::query(
                "UPDATE bots SET name = ?, token = ?, is_active = ? WHERE id = ?",
                [$this->name, $this->token, $this->is_active ? 1 : 0, $this->id]
            );
            Logger::info("Bot updated: {$this->name} (ID: {$this->id})");
            return true;
        } catch (\PDOException $e) {
            Logger::error("Failed to update bot: " . $e->getMessage());
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            Database::query("DELETE FROM bots WHERE id = ?", [$this->id]);
            Logger::info("Bot deleted: {$this->name} (ID: {$this->id})");
            return true;
        } catch (\PDOException $e) {
            Logger::error("Failed to delete bot: " . $e->getMessage());
            return false;
        }
    }

    public function getChannels(): array
    {
        return Channel::findByBotId($this->id);
    }

    private static function fromArray(array $data): self
    {
        $bot = new self();
        $bot->id = (int)$data['id'];
        $bot->name = $data['name'];
        $bot->token = $data['token'];
        $bot->created_at = $data['created_at'];
        $bot->is_active = (bool)$data['is_active'];
        return $bot;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'token' => $this->token,
            'created_at' => $this->created_at,
            'is_active' => $this->is_active,
        ];
    }
}
