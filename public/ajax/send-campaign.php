<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/CampaignController.php';

use Config\Database;
use App\Controllers\CampaignController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT);
$campaignId = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);

if (!$campaignId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da campanha inválido ou ausente.']);
    exit;
}

try {
    $db = Database::getConnection();
    $campaignController = new CampaignController($db);

    // PHP 8.2 Match Expression estruturando ações de disparo
    echo match ($action) {
        'test' => (function() use ($campaignController, $campaignId) {
            $testEmail = filter_input(INPUT_POST, 'test_email', FILTER_VALIDATE_EMAIL);
            if (!$testEmail) {
                return json_encode(['success' => false, 'message' => 'E-mail de destino inválido para o teste.']);
            }
            return $campaignController->sendTest($campaignId, $testEmail);
        })(),
        
        'release' => (function() use ($campaignController, $campaignId) {
            // Caso queira disparar para a fila inteira via Ajax futuramente
            $campaignController->processQueue($campaignId);
            return json_encode(['success' => true, 'message' => 'Campanha processada e enviada para a fila com sucesso.']);
        })(),

        default => (function() {
            http_response_code(400);
            return json_encode(['success' => false, 'message' => 'Ação de campanha não especificada ou inválida.']);
        })()
    };

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao processar e-mail: ' . $e->getMessage()]);
}