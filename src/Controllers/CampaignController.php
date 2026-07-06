<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\View;

final class CampaignController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $campaigns = Campaign::forEvent((int) $eventId);
        $templates = EmailTemplate::forEvent((int) $eventId);

        View::render('promoter/campaigns', [
            'pageTitle' => 'Campanhas — ' . $event['name'],
            'event' => $event,
            'campaigns' => $campaigns,
            'templates' => $templates,
        ]);
    }

    public function create(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $templateId = (int) ($_POST['template_id'] ?? 0);
        $template = EmailTemplate::find($templateId);

        if (!$template || (int) $template['event_id'] !== (int) $eventId) {
            $_SESSION['flash_error'] = 'Selecione um template válido.';
            header('Location: /promoter/events/' . $eventId . '/campaigns');
            return;
        }

        $campaignId = Campaign::create([
            'event_id' => (int) $eventId,
            'template_id' => $templateId,
            'name' => trim($_POST['name'] ?? $template['name']),
            'status' => 'draft',
        ]);

        $audience = $_POST['audience'] ?? 'all';
        foreach (Contact::forEvent((int) $eventId) as $contact) {
            if ($audience === 'pending' && $contact['rsvp_status'] !== 'pending') {
                continue;
            }
            CampaignRecipient::enqueue($campaignId, (int) $contact['id']);
        }

        $_SESSION['flash_success'] = 'Campanha criada com os destinatários enfileirados.';
        header('Location: /promoter/campaigns/' . $campaignId);
    }

    public function show(string $campaignId): void
    {
        $campaign = Campaign::find((int) $campaignId);
        if (!$campaign) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/views/errors/404.php';
            return;
        }

        $event = $this->ownedEventOrFail((string) $campaign['event_id']);
        $stats = Campaign::stats((int) $campaignId);
        $recipients = CampaignRecipient::forCampaign((int) $campaignId);

        View::render('promoter/campaign_detail', [
            'pageTitle' => 'Campanha — ' . $campaign['name'],
            'event' => $event,
            'campaign' => $campaign,
            'stats' => $stats,
            'recipients' => $recipients,
        ]);
    }

    public function send(string $campaignId): void
    {
        $campaign = Campaign::find((int) $campaignId);
        if (!$campaign) {
            http_response_code(404);
            return;
        }

        $this->ownedEventOrFail((string) $campaign['event_id']);
        Csrf::requireValid();

        Campaign::updateStatus((int) $campaignId, 'queued');
        $_SESSION['flash_success'] = 'Campanha enfileirada para envio. O disparo respeita o limite de mensagens/hora do seu perfil.';
        header('Location: /promoter/campaigns/' . $campaignId);
    }

    public function pause(string $campaignId): void
    {
        $campaign = Campaign::find((int) $campaignId);
        if (!$campaign) {
            http_response_code(404);
            return;
        }

        $this->ownedEventOrFail((string) $campaign['event_id']);
        Csrf::requireValid();

        Campaign::updateStatus((int) $campaignId, 'paused');
        $_SESSION['flash_success'] = 'Campanha pausada.';
        header('Location: /promoter/campaigns/' . $campaignId);
    }
}
