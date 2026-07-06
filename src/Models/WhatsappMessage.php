<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

/**
 * Estrutura preparada para disparo de WhatsApp. Sem integração real com provedor ainda:
 * as mensagens ficam registradas como 'simulated_sent' para permitir montar todo o fluxo
 * (composição, fila, histórico) antes de plugar uma API de fato.
 */
final class WhatsappMessage
{
    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT w.*, c.name AS contact_name, c.phone AS contact_phone
             FROM whatsapp_messages w JOIN contacts c ON c.id = w.contact_id
             WHERE w.event_id = ? ORDER BY w.created_at DESC'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function createForContacts(int $eventId, array $contactIds, string $messageText): int
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO whatsapp_messages (event_id, contact_id, message_text, status, sent_at)
             VALUES (?, ?, ?, 'simulated_sent', NOW())"
        );

        $count = 0;
        foreach ($contactIds as $contactId) {
            $stmt->execute([$eventId, (int) $contactId, $messageText]);
            $count++;
        }

        return $count;
    }
}
