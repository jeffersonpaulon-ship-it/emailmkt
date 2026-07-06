<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\EmailTemplate;
use App\View;

final class TemplateController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $templates = EmailTemplate::forEvent((int) $eventId);

        View::render('promoter/templates', [
            'pageTitle' => 'Templates — ' . $event['name'],
            'event' => $event,
            'templates' => $templates,
        ]);
    }

    public function create(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        EmailTemplate::create([
            'promoter_id' => Auth::id(),
            'event_id' => (int) $eventId,
            'name' => trim($_POST['name'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'body_html' => $_POST['body_html'] ?? '',
        ]);

        $_SESSION['flash_success'] = 'Template criado. Use {{nome}}, {{evento}} e {{link_confirmacao}} no corpo para personalizar.';
        header('Location: /promoter/events/' . $eventId . '/templates');
    }

    public function delete(string $eventId, string $templateId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $template = EmailTemplate::find((int) $templateId);
        if ($template && (int) $template['event_id'] === (int) $eventId) {
            EmailTemplate::delete((int) $templateId);
            $_SESSION['flash_success'] = 'Template removido.';
        }

        header('Location: /promoter/events/' . $eventId . '/templates');
    }
}
