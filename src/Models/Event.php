<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Event
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM events WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function forPromoter(int $promoterId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM events WHERE promoter_id = ? ORDER BY event_date DESC, id DESC');
        $stmt->execute([$promoterId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO events (promoter_id, name, description, event_date, location, slug, status)
             VALUES (:promoter_id, :name, :description, :event_date, :location, :slug, :status)'
        );
        $stmt->execute([
            'promoter_id' => $data['promoter_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'event_date' => $data['event_date'] ?: null,
            'location' => $data['location'] ?? null,
            'slug' => $data['slug'],
            'status' => $data['status'] ?? 'draft',
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE events SET name = :name, description = :description, event_date = :event_date,
             location = :location, slug = :slug, status = :status WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'event_date' => $data['event_date'] ?: null,
            'location' => $data['location'] ?? null,
            'slug' => $data['slug'],
            'status' => $data['status'] ?? 'draft',
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = Database::connection()->prepare('SELECT id FROM events WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = Database::connection()->prepare('SELECT id FROM events WHERE slug = ?');
            $stmt->execute([$slug]);
        }

        return (bool) $stmt->fetch();
    }
}
