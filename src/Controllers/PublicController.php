<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Csrf;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Page;
use App\View;

final class PublicController
{
    public function confirmation(string $slug): void
    {
        $page = Page::findBySlug($slug);
        if (!$page || $page['type'] !== 'confirmation') {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/views/errors/404.php';
            return;
        }

        $event = Event::find((int) $page['event_id']);
        $prefill = ['name' => '', 'email' => '', 'phone' => '', 'guests_count' => 0];
        $token = $_GET['t'] ?? '';

        if ($token !== '') {
            $recipient = CampaignRecipient::findByToken($token);
            if ($recipient) {
                CampaignRecipient::registerClick($token, $_SERVER['REQUEST_URI'] ?? '', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
                $contact = Contact::find((int) $recipient['contact_id']);
                if ($contact) {
                    $prefill = [
                        'name' => $contact['name'],
                        'email' => $contact['email'],
                        'phone' => $contact['phone'] ?? '',
                        'guests_count' => $contact['guests_count'],
                    ];
                }
            }
        }

        View::render('public/confirmation', [
            'pageTitle' => $page['title'],
            'page' => $page,
            'event' => $event,
            'prefill' => $prefill,
            'token' => $token,
        ], null);
    }

    public function confirmationSubmit(string $slug): void
    {
        Csrf::requireValid();

        $page = Page::findBySlug($slug);
        if (!$page || $page['type'] !== 'confirmation') {
            http_response_code(404);
            return;
        }

        $eventId = (int) $page['event_id'];
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $guests = max(0, (int) ($_POST['guests_count'] ?? 0));
        $status = ($_POST['attending'] ?? 'yes') === 'yes' ? 'confirmed' : 'declined';

        $existing = Contact::findByEmail($eventId, $email);

        if ($existing) {
            Contact::updateRsvp((int) $existing['id'], $status, $guests, $phone ?: null, $name ?: null, 'confirmation_page');
        } else {
            Contact::create([
                'event_id' => $eventId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'guests_count' => $guests,
                'source' => 'confirmation_page',
                'rsvp_status' => $status,
                'rsvp_at' => date('Y-m-d H:i:s'),
            ]);
        }

        View::render('public/success', [
            'pageTitle' => $page['title'],
            'message' => $page['success_message'] ?: 'Presença confirmada! Obrigado.',
        ], null);
    }

    public function capture(string $slug): void
    {
        $page = Page::findBySlug($slug);
        if (!$page || $page['type'] !== 'capture') {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/views/errors/404.php';
            return;
        }

        View::render('public/capture', [
            'pageTitle' => $page['title'],
            'page' => $page,
        ], null);
    }

    public function captureSubmit(string $slug): void
    {
        Csrf::requireValid();

        $page = Page::findBySlug($slug);
        if (!$page || $page['type'] !== 'capture') {
            http_response_code(404);
            return;
        }

        $eventId = (int) $page['event_id'];
        $email = trim($_POST['email'] ?? '');
        $existing = Contact::findByEmail($eventId, $email);

        if (!$existing) {
            Contact::create([
                'event_id' => $eventId,
                'name' => trim($_POST['name'] ?? ''),
                'email' => $email,
                'phone' => trim($_POST['phone'] ?? ''),
                'source' => 'capture_page',
                'rsvp_status' => 'pending',
            ]);
        }

        View::render('public/success', [
            'pageTitle' => $page['title'],
            'message' => $page['success_message'] ?: 'Recebemos seu cadastro!',
        ], null);
    }
}
