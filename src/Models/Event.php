<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Event
{
    // PHP 8.2: Injeção de dependência simplificada via Constructor
    public function __construct(private PDO $db) {}

    /**
     * Cria um novo evento no sistema
     */
    public function create(int $clientId, int $promoterId, string $title, string $eventDate, ?string $location = null): int
    {
        $sql = "INSERT INTO events (client_id, promoter_id, title, event_date, location) 
                VALUES (:client_id, :promoter_id, :title, :event_date, :location)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'client_id'   => $clientId,
            'promoter_id' => $promoterId,
            'title'       => $title,
            'event_date'  => $eventDate,
            'location'    => $location
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Retorna os dados básicos de um evento específico
     */
    public function findById(int $eventId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();
        
        return $event ?: null;
    }

    /**
     * Consolida TODAS as métricas do evento para o Dashboard do Cliente
     * Retorna totais de RSVP, canais de contato e taxas de engajamento de e-mail
     */
    public function getDashboardMetrics(int $eventId): array
    {
        $metrics = [
            'guests' => [
                'total' => 0,
                'confirmed' => 0,
                'declined' => 0,
                'pending' => 0,
                'total_tickets' => 0
            ],
            'email_engagement' => [
                'total_opens' => 0,
                'total_clicks' => 0
            ]
        ];

        // 1. Métricas de RSVP e Ingressos
        $sqlGuests = "SELECT 
                        COUNT(id) as total_guests,
                        SUM(tickets_count) as total_tickets,
                        SUM(CASE WHEN confirmation_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN confirmation_status = 'declined' THEN 1 ELSE 0 END) as declined,
                        SUM(CASE WHEN confirmation_status = 'pending' THEN 1 ELSE 0 END) as pending
                      FROM guests 
                      WHERE event_id = ?";
        
        $stmt = $this->db->prepare($sqlGuests);
        $stmt->execute([$eventId]);
        $guestData = $stmt->fetch();

        if ($guestData) {
            $metrics['guests'] = [
                'total'         => (int)$guestData['total_guests'],
                'total_tickets' => (int)($guestData['total_tickets'] ?? 0),
                'confirmed'     => (int)$guestData['confirmed'],
                'declined'      => (int)$guestData['declined'],
                'pending'       => (int)$guestData['pending'],
            ];
        }

        // 2. Métricas de Abertura e Cliques de E-mail vinculados a este evento
        $sqlTracking = "SELECT 
                            SUM(CASE WHEN t.action_type = 'open' THEN 1 ELSE 0 END) as opens,
                            SUM(CASE WHEN t.action_type = 'click' THEN 1 ELSE 0 END) as clicks
                        FROM campaign_tracking t
                        JOIN email_campaigns c ON t.campaign_id = c.id
                        WHERE c.event_id = ?";
        
        $stmt = $this->db->prepare($sqlTracking);
        $stmt->execute([$eventId]);
        $trackingData = $stmt->fetch();

        if ($trackingData) {
            $metrics['email_engagement'] = [
                'total_opens'  => (int)($trackingData['opens'] ?? 0),
                'total_clicks' => (int)($trackingData['clicks'] ?? 0),
            ];
        }

        return $metrics;
    }
}