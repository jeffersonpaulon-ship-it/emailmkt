<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

use App\Auth;
use App\Models\Event;

trait EventOwnership
{
    /**
     * Garante que o evento existe e pertence ao promotor autenticado. Encerra a requisição
     * com 404 caso contrário, para que um promotor nunca acesse dados de outro.
     */
    private function ownedEventOrFail(int|string $eventId): array
    {
        $event = Event::find((int) $eventId);

        if (!$event || (int) $event['promoter_id'] !== Auth::id()) {
            http_response_code(404);
            require dirname(__DIR__, 3) . '/views/errors/404.php';
            exit;
        }

        return $event;
    }
}
