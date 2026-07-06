<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Models\Campaign;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Visibility;
use App\Models\WhatsappMessage;
use App\View;

final class ClientController
{
    public function __construct()
    {
        Auth::requireRole('client');
    }

    public function index(): void
    {
        $events = Visibility::eventsForClient(Auth::id());
        View::render('client/dashboard', ['pageTitle' => 'Meus eventos', 'events' => $events]);
    }

    public function show(string $eventId): void
    {
        $access = Visibility::forEventAndClient((int) $eventId, Auth::id());
        $event = $access ? Event::find((int) $eventId) : null;

        if (!$access || !$event) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/views/errors/404.php';
            return;
        }

        $contactStats = $access['can_view_contacts'] ? Contact::stats((int) $eventId) : null;
        $campaigns = $access['can_view_email_stats'] ? Campaign::forEvent((int) $eventId) : [];
        $whatsappMessages = $access['can_view_whatsapp'] ? WhatsappMessage::forEvent((int) $eventId) : [];
        $calls = $access['can_view_calls'] ? CallLog::forEvent((int) $eventId) : [];

        View::render('client/event_detail', [
            'pageTitle' => $event['name'],
            'event' => $event,
            'access' => $access,
            'contactStats' => $contactStats,
            'campaigns' => $campaigns,
            'whatsappMessages' => $whatsappMessages,
            'calls' => $calls,
        ]);
    }
}
