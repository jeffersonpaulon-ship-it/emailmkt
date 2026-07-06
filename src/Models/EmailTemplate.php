<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class EmailTemplate
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM email_templates WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM email_templates WHERE event_id = ? ORDER BY created_at DESC');
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO email_templates (promoter_id, event_id, name, subject, body_html)
             VALUES (:promoter_id, :event_id, :name, :subject, :body_html)'
        );
        $stmt->execute([
            'promoter_id' => $data['promoter_id'],
            'event_id' => $data['event_id'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body_html' => $data['body_html'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE email_templates SET name = :name, subject = :subject, body_html = :body_html WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body_html' => $data['body_html'],
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM email_templates WHERE id = ?')->execute([$id]);
    }
}
