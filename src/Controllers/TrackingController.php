<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class TrackingController 
{
    public function __construct(private PDO $db) {}

    // Registra a abertura capturada pelo Pixel oculto
    public function logOpen(int $campaignId, int $guestId, ?string $ip, ?string $userAgent): void 
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaign_tracking (campaign_id, guest_id, action_type, ip_address, user_agent) 
            VALUES (:campaign_id, :guest_id, 'open', :ip, :ua)
        ");
        
        $stmt->execute([
            'campaign_id' => $campaignId,
            'guest_id'    => $guestId,
            'ip'          => $ip,
            'ua'          => $userAgent
        ]);
    }

    // Registra o clique e retorna a URL real para onde o convidado deve ir
    public function logClick(int $campaignId, int $guestId, string $targetUrl, ?string $ip, ?string $userAgent): string 
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaign_tracking (campaign_id, guest_id, action_type, target_url, ip_address, user_agent) 
            VALUES (:campaign_id, :guest_id, 'click', :target_url, :ip, :ua)
        ");
        
        $stmt->execute([
            'campaign_id' => $campaignId,
            'guest_id'    => $guestId,
            'target_url'  => $targetUrl,
            'ip'          => $ip,
            'ua'          => $userAgent
        ]);

        // Retorna a URL limpa decodificada para o redirecionamento header('Location: ...')
        return htmlspecialchars_decode($targetUrl);
    }
}