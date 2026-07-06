<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\Contact;
use App\Url;
use App\View;

final class ContactController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $contacts = Contact::forEvent((int) $eventId);

        View::render('promoter/contacts', [
            'pageTitle' => 'Contatos — ' . $event['name'],
            'event' => $event,
            'contacts' => $contacts,
        ]);
    }

    public function create(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $email = trim($_POST['email'] ?? '');

        if (Contact::findByEmail((int) $eventId, $email)) {
            $_SESSION['flash_error'] = 'Já existe um contato com este e-mail neste evento.';
            header('Location: ' . Url::to('/promoter/events/' . $eventId . '/contacts'));
            return;
        }

        Contact::create([
            'event_id' => (int) $eventId,
            'name' => trim($_POST['name'] ?? ''),
            'email' => $email,
            'phone' => trim($_POST['phone'] ?? ''),
            'source' => 'manual',
        ]);

        $_SESSION['flash_success'] = 'Contato adicionado.';
        header('Location: ' . Url::to('/promoter/events/' . $eventId . '/contacts'));
    }

    public function delete(string $eventId, string $contactId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $contact = Contact::find((int) $contactId);
        if ($contact && (int) $contact['event_id'] === (int) $eventId) {
            Contact::delete((int) $contactId);
            $_SESSION['flash_success'] = 'Contato removido.';
        }

        header('Location: ' . Url::to('/promoter/events/' . $eventId . '/contacts'));
    }
}
