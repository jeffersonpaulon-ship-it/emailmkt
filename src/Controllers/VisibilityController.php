<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\User;
use App\Models\Visibility;
use App\View;

final class VisibilityController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $clients = User::clientsOfPromoter(Auth::id());
        $visibility = [];
        foreach (Visibility::forEvent((int) $eventId) as $v) {
            $visibility[(int) $v['client_id']] = $v;
        }

        View::render('promoter/visibility', [
            'pageTitle' => 'Visibilidade — ' . $event['name'],
            'event' => $event,
            'clients' => $clients,
            'visibility' => $visibility,
        ]);
    }

    public function update(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $client = User::find($clientId);

        if (!$client || $client['role'] !== 'client' || (int) $client['promoter_id'] !== Auth::id()) {
            $_SESSION['flash_error'] = 'Cliente inválido.';
            header('Location: /promoter/events/' . $eventId . '/visibility');
            return;
        }

        if (isset($_POST['revoke'])) {
            Visibility::revoke((int) $eventId, $clientId);
            $_SESSION['flash_success'] = 'Acesso do cliente a este evento removido.';
        } else {
            Visibility::upsert((int) $eventId, $clientId, [
                'can_view_contacts' => isset($_POST['can_view_contacts']),
                'can_view_email_stats' => isset($_POST['can_view_email_stats']),
                'can_view_whatsapp' => isset($_POST['can_view_whatsapp']),
                'can_view_calls' => isset($_POST['can_view_calls']),
            ]);
            $_SESSION['flash_success'] = 'Visibilidade do cliente atualizada.';
        }

        header('Location: /promoter/events/' . $eventId . '/visibility');
    }
}
