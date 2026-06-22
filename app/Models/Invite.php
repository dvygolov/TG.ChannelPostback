<?php

namespace App\Models;

use App\Database;
use App\Logger;

class Invite
{
    public ?int $id = null;
    public int $channel_id;
    public string $subid;
    public string $invite_link;
    public string $status = 'pending'; // pending, subscribed, expired
    public ?int $user_telegram_id = null;
    public string $created_at;
    public ?string $subscribed_at = null;

    public static function create(
        int $channel_id,
        string $subid,
        string $invite_link
    ): ?self {
        try {
            Database::query(
                "INSERT INTO invites (channel_id, subid, invite_link) VALUES (?, ?, ?)",
                [$channel_id, $subid, $invite_link]
            );
            
            $id = Database::lastInsertId();
            Logger::info("Invite created: subid={$subid}, link={$invite_link}");
            
            return self::findById((int)$id);
        } catch (\PDOException $e) {
            Logger::error("Failed to create invite: " . $e->getMessage());
            return null;
        }
    }

    public static function findById(int $id): ?self
    {
        $row = Database::fetchOne("SELECT * FROM invites WHERE id = ?", [$id]);
        return $row ? self::fromArray($row) : null;
    }

    public static function findBySubid(string $subid): ?self
    {
        $row = Database::fetchOne("SELECT * FROM invites WHERE subid = ?", [$subid]);
        return $row ? self::fromArray($row) : null;
    }

    public static function findByInviteLink(string $invite_link): ?self
    {
        $row = Database::fetchOne("SELECT * FROM invites WHERE invite_link = ?", [$invite_link]);
        return $row ? self::fromArray($row) : null;
    }

    public static function findByChannelId(int $channel_id, int $limit = 100): array
    {
        $rows = Database::fetchAll(
            "SELECT * FROM invites WHERE channel_id = ? ORDER BY created_at DESC LIMIT ?",
            [$channel_id, $limit]
        );
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    public static function getStats(): array
    {
        $stats = Database::fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'subscribed' THEN 1 ELSE 0 END) as subscribed
            FROM invites
        ");
        
        return $stats ?: ['total' => 0, 'pending' => 0, 'subscribed' => 0];
    }

    public function markAsSubscribed(int $user_telegram_id): bool
    {
        try {
            Database::query(
                "UPDATE invites SET status = 'subscribed', user_telegram_id = ?, subscribed_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$user_telegram_id, $this->id]
            );
            
            $this->status = 'subscribed';
            $this->user_telegram_id = $user_telegram_id;
            
            Logger::info("Invite marked as subscribed: subid={$this->subid}, user_id={$user_telegram_id}");
            return true;
        } catch (\PDOException $e) {
            Logger::error("Failed to mark invite as subscribed: " . $e->getMessage());
            return false;
        }
    }

    public function getChannel(): ?Channel
    {
        return Channel::findById($this->channel_id);
    }

    private static function fromArray(array $data): self
    {
        $invite = new self();
        $invite->id = (int)$data['id'];
        $invite->channel_id = (int)$data['channel_id'];
        $invite->subid = $data['subid'];
        $invite->invite_link = $data['invite_link'];
        $invite->status = $data['status'];
        $invite->user_telegram_id = $data['user_telegram_id'] ? (int)$data['user_telegram_id'] : null;
        $invite->created_at = $data['created_at'];
        $invite->subscribed_at = $data['subscribed_at'];
        return $invite;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'channel_id' => $this->channel_id,
            'subid' => $this->subid,
            'invite_link' => $this->invite_link,
            'status' => $this->status,
            'user_telegram_id' => $this->user_telegram_id,
            'created_at' => $this->created_at,
            'subscribed_at' => $this->subscribed_at,
        ];
    }
}
