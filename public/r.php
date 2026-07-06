<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/TrackingController.php';

use Config\Database;
use App\Controllers\TrackingController;

// Captura os parâmetros via GET
$campaignId = filter_input(INPUT_GET, 'c', FILTER_VALIDATE_INT);
$guestId    = filter_input(INPUT_GET, 'g', FILTER_VALIDATE_INT);
$encodedUrl = $_GET['url'] ?? '';

// Fallback caso os dados essenciais não estejam presentes
if (!$campaignId || !$guestId || empty($encodedUrl)) {
    // Redireciona para a home do RSVP caso a requisição seja malformada
    header("Location: https://wnebr.com/rsvp/");
    exit;
}

// Decodifica a URL alvo (ex: de %3A%2F%2F para ://)
$targetUrl = rawurldecode($encodedUrl);

// Validação básica de segurança para garantir que seja uma URL estruturada
if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
    header("Location: https://wnebr.com/rsvp/");
    exit;
}

try {
    $db = Database::getConnection();
    $trackingController = new TrackingController($db);

    // Registra o clique no banco de dados e retorna a URL sanitizada
    $redirectUrl = $trackingController->logClick(
        (int)$campaignId,
        (int)$guestId,
        $targetUrl,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    );

    // Executa o redirecionamento HTTP 302 (temporário, ideal para analytics)
    header("Location: " . $redirectUrl, true, 302);
    exit;

} catch (Exception $e) {
    // Em caso de falha crítica no banco, prioriza a experiência do usuário e redireciona assim mesmo
    header("Location: " . $targetUrl, true, 302);
    exit;
}