<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

/**
 * Estrutura preparada para acompanhamento de ligações. Sem discagem automática real ainda:
 * o promotor registra manualmente o resultado do contato telefônico feito fora do sistema.
 */
final class CallLog
{
    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT cl.*, c.name AS contact_name, c.phone AS contact_phone
             FROM calls cl JOIN contacts c ON c.id = cl.contact_id
             WHERE cl.event_id = ? ORDER BY cl.created_at DESC'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function create(int $eventId, int $contactId, string $outcome, ?string $notes): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO calls (event_id, contact_id, outcome, notes) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$eventId, $contactId, $outcome, $notes]);
        return (int) Database::connection()->lastInsertId();
    }
}
