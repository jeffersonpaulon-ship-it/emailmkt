<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use Config\Database;

$campaign_id = filter_input(INPUT_GET, 'c', FILTER_VALIDATE_INT);
$guest_id    = filter_input(INPUT_GET, 'g', FILTER_VALIDATE_INT);

if ($campaign_id && $guest_id) {
    $db = Database::getConnection();
    
    // Registra a abertura no banco
    $stmt = $db->prepare("
        INSERT INTO campaign_tracking (campaign_id, guest_id, action_type, ip_address, user_agent) 
        VALUES (:campaign_id, :guest_id, 'open', :ip, :ua)
    ");
    
    $stmt->execute([
        'campaign_id' => $campaign_id,
        'guest_id'    => $guest_id,
        'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua'          => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Retorna uma imagem GIF transparente de 1x1 pixel
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
exit;