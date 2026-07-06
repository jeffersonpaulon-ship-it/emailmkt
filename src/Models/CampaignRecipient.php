<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class CampaignRecipient
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM campaign_recipients WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM campaign_recipients WHERE tracking_token = ? LIMIT 1');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function forCampaign(int $campaignId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT cr.*, c.name AS contact_name, c.email AS contact_email
             FROM campaign_recipients cr
             JOIN contacts c ON c.id = cr.contact_id
             WHERE cr.campaign_id = ? ORDER BY cr.id'
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public static function enqueue(int $campaignId, int $contactId): void
    {
        $token = bin2hex(random_bytes(20));
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO campaign_recipients (campaign_id, contact_id, tracking_token, status)
             VALUES (?, ?, ?, "pending")'
        );
        $stmt->execute([$campaignId, $contactId, $token]);
    }

    /**
     * Busca até $limit destinatários pendentes de promotores que ainda não atingiram
     * o limite de mensagens/hora configurado pelo admin.
     */
    public static function nextPendingBatch(int $limit): array
    {
        $sql = "SELECT cr.*, c.email AS contact_email, c.name AS contact_name,
                       camp.template_id, camp.event_id, ev.promoter_id
                FROM campaign_recipients cr
                JOIN contacts c ON c.id = cr.contact_id
                JOIN campaigns camp ON camp.id = cr.campaign_id
                JOIN events ev ON ev.id = camp.event_id
                WHERE cr.status = 'pending' AND camp.status IN ('queued', 'sending')
                ORDER BY cr.id
                LIMIT " . (int) $limit;

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function sentLastHourByPromoter(int $promoterId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM campaign_recipients cr
             JOIN campaigns camp ON camp.id = cr.campaign_id
             JOIN events ev ON ev.id = camp.event_id
             WHERE ev.promoter_id = ? AND cr.sent_at >= (NOW() - INTERVAL 1 HOUR)"
        );
        $stmt->execute([$promoterId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markSent(int $id): void
    {
        Database::connection()->prepare("UPDATE campaign_recipients SET status = 'sent', sent_at = NOW() WHERE id = ?")->execute([$id]);
    }

    public static function markFailed(int $id, string $error): void
    {
        Database::connection()->prepare("UPDATE campaign_recipients SET status = 'failed', error_message = ? WHERE id = ?")
            ->execute([substr($error, 0, 255), $id]);
    }

    public static function registerOpen(string $token, ?string $ip, ?string $userAgent): void
    {
        $recipient = self::findByToken($token);
        if (!$recipient) {
            return;
        }

        $db = Database::connection();
        $db->prepare(
            "UPDATE campaign_recipients SET
                status = IF(status = 'clicked', 'clicked', 'opened'),
                opened_at = COALESCE(opened_at, NOW()),
                open_count = open_count + 1
             WHERE id = ?"
        )->execute([$recipient['id']]);

        $db->prepare('INSERT INTO email_events (campaign_recipient_id, type, ip_address, user_agent) VALUES (?, "open", ?, ?)')
            ->execute([$recipient['id'], $ip, $userAgent]);
    }

    public static function registerClick(string $token, string $url, ?string $ip, ?string $userAgent): void
    {
        $recipient = self::findByToken($token);
        if (!$recipient) {
            return;
        }

        $db = Database::connection();
        $db->prepare(
            "UPDATE campaign_recipients SET
                status = 'clicked',
                first_clicked_at = COALESCE(first_clicked_at, NOW()),
                click_count = click_count + 1
             WHERE id = ?"
        )->execute([$recipient['id']]);

        $db->prepare('INSERT INTO email_events (campaign_recipient_id, type, url, ip_address, user_agent) VALUES (?, "click", ?, ?, ?)')
            ->execute([$recipient['id'], $url, $ip, $userAgent]);
    }
}
