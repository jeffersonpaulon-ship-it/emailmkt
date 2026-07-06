<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Contact
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contacts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contacts WHERE event_id = ? ORDER BY created_at DESC');
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function findByEmail(int $eventId, string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contacts WHERE event_id = ? AND email = ? LIMIT 1');
        $stmt->execute([$eventId, $email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO contacts (event_id, name, email, phone, guests_count, source, rsvp_status, rsvp_at)
             VALUES (:event_id, :name, :email, :phone, :guests_count, :source, :rsvp_status, :rsvp_at)'
        );
        $stmt->execute([
            'event_id' => $data['event_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'guests_count' => $data['guests_count'] ?? 0,
            'source' => $data['source'] ?? 'manual',
            'rsvp_status' => $data['rsvp_status'] ?? 'pending',
            'rsvp_at' => $data['rsvp_at'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function updateRsvp(int $id, string $status, int $guestsCount, ?string $phone, ?string $name, string $source): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE contacts SET rsvp_status = :status, guests_count = :guests_count, phone = COALESCE(:phone, phone),
             name = COALESCE(:name, name), source = :source, rsvp_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'guests_count' => $guestsCount,
            'phone' => $phone,
            'name' => $name,
            'source' => $source,
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM contacts WHERE id = ?')->execute([$id]);
    }

    public static function stats(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(rsvp_status = 'confirmed') AS confirmed,
                SUM(rsvp_status = 'declined') AS declined,
                SUM(rsvp_status = 'pending') AS pending
             FROM contacts WHERE event_id = ?"
        );
        $stmt->execute([$eventId]);
        return $stmt->fetch() ?: ['total' => 0, 'confirmed' => 0, 'declined' => 0, 'pending' => 0];
    }
}
