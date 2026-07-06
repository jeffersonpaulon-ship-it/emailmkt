<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Page
{
    public static function findByEventAndType(int $eventId, string $type): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pages WHERE event_id = ? AND type = ? LIMIT 1');
        $stmt->execute([$eventId, $type]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = Database::connection()->prepare('SELECT id FROM pages WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = Database::connection()->prepare('SELECT id FROM pages WHERE slug = ?');
            $stmt->execute([$slug]);
        }

        return (bool) $stmt->fetch();
    }

    public static function upsert(int $eventId, string $type, array $data): void
    {
        $existing = self::findByEventAndType($eventId, $type);

        if ($existing) {
            $stmt = Database::connection()->prepare(
                'UPDATE pages SET slug = :slug, title = :title, intro_text = :intro_text,
                 success_message = :success_message, is_active = :is_active WHERE id = :id'
            );
            $stmt->execute([
                'id' => $existing['id'],
                'slug' => $data['slug'],
                'title' => $data['title'],
                'intro_text' => $data['intro_text'] ?? null,
                'success_message' => $data['success_message'] ?? null,
                'is_active' => $data['is_active'] ?? 1,
            ]);
            return;
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO pages (event_id, type, slug, title, intro_text, success_message, is_active)
             VALUES (:event_id, :type, :slug, :title, :intro_text, :success_message, :is_active)'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'type' => $type,
            'slug' => $data['slug'],
            'title' => $data['title'],
            'intro_text' => $data['intro_text'] ?? null,
            'success_message' => $data['success_message'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ]);
    }
}
