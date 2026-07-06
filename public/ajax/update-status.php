<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Guest.php';

use Config\Database;
use App\Models\Guest;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$guestId = filter_input(INPUT_POST, 'guest_id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'confirmation_status', FILTER_DEFAULT);

if (!$guestId || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos para a atualização de status.']);
    exit;
}

try {
    $db = Database::getConnection();
    $guestModel = new Guest($db);

    $updated = $guestModel->updateConfirmationStatus($guestId, trim($status));

    if ($updated) {
        echo json_encode(['success' => true, 'message' => 'Confirmação gravada com sucesso!']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status deste convidado.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar status: ' . $e->getMessage()]);
}