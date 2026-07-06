<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Visibility
{
    public static function forEventAndClient(int $eventId, int $clientId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM event_client_visibility WHERE event_id = ? AND client_id = ? LIMIT 1');
        $stmt->execute([$eventId, $clientId]);
        return $stmt->fetch() ?: null;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT v.*, u.name AS client_name, u.email AS client_email
             FROM event_client_visibility v JOIN users u ON u.id = v.client_id
             WHERE v.event_id = ? ORDER BY u.name'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function eventsForClient(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT ev.*, v.can_view_contacts, v.can_view_email_stats, v.can_view_whatsapp, v.can_view_calls
             FROM event_client_visibility v
             JOIN events ev ON ev.id = v.event_id
             WHERE v.client_id = ? ORDER BY ev.event_date DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $eventId, int $clientId, array $flags): void
    {
        $existing = self::forEventAndClient($eventId, $clientId);
        $params = [
            'event_id' => $eventId,
            'client_id' => $clientId,
            'can_view_contacts' => !empty($flags['can_view_contacts']) ? 1 : 0,
            'can_view_email_stats' => !empty($flags['can_view_email_stats']) ? 1 : 0,
            'can_view_whatsapp' => !empty($flags['can_view_whatsapp']) ? 1 : 0,
            'can_view_calls' => !empty($flags['can_view_calls']) ? 1 : 0,
        ];

        if ($existing) {
            Database::connection()->prepare(
                'UPDATE event_client_visibility SET can_view_contacts = :can_view_contacts,
                 can_view_email_stats = :can_view_email_stats, can_view_whatsapp = :can_view_whatsapp,
                 can_view_calls = :can_view_calls WHERE event_id = :event_id AND client_id = :client_id'
            )->execute($params);
            return;
        }

        Database::connection()->prepare(
            'INSERT INTO event_client_visibility (event_id, client_id, can_view_contacts, can_view_email_stats, can_view_whatsapp, can_view_calls)
             VALUES (:event_id, :client_id, :can_view_contacts, :can_view_email_stats, :can_view_whatsapp, :can_view_calls)'
        )->execute($params);
    }

    public static function revoke(int $eventId, int $clientId): void
    {
        Database::connection()->prepare('DELETE FROM event_client_visibility WHERE event_id = ? AND client_id = ?')
            ->execute([$eventId, $clientId]);
    }
}
