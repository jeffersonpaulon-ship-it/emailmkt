<?php
declare(strict_types=1);

// Configuração de cabeçalho para resposta JSON estrita
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/ExcelController.php';

use Config\Database;
use App\Controllers\ExcelController;

// Garante que o método seja estritamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

// Captura e valida os inputs via filtro nativo do PHP
$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$file = $_FILES['excel_file'] ?? null;

if (!$eventId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do evento inválido ou ausente.']);
    exit;
}

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.']);
    exit;
}

try {
    $db = Database::getConnection();
    $excelController = new ExcelController($db);
    
    // O controller já processa internamente a transação e retorna a string JSON pronta
    echo $excelController->import($eventId, $file);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}