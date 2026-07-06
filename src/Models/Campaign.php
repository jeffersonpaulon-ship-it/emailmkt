<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Campaign
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM campaigns WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM campaigns WHERE event_id = ? ORDER BY created_at DESC');
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO campaigns (event_id, template_id, name, status) VALUES (:event_id, :template_id, :name, :status)'
        );
        $stmt->execute([
            'event_id' => $data['event_id'],
            'template_id' => $data['template_id'],
            'name' => $data['name'],
            'status' => $data['status'] ?? 'draft',
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::connection()->prepare('UPDATE campaigns SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function stats(int $campaignId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'pending') AS pending,
                SUM(status IN ('sent','opened','clicked')) AS sent,
                SUM(status IN ('opened','clicked')) AS opened,
                SUM(status = 'clicked') AS clicked,
                SUM(status = 'failed') AS failed
             FROM campaign_recipients WHERE campaign_id = ?"
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetch() ?: [];
    }
}
