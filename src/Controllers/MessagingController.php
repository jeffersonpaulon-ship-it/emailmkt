<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\WhatsappMessage;
use App\View;

final class MessagingController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function whatsapp(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $contacts = Contact::forEvent((int) $eventId);
        $messages = WhatsappMessage::forEvent((int) $eventId);

        View::render('promoter/whatsapp', [
            'pageTitle' => 'WhatsApp — ' . $event['name'],
            'event' => $event,
            'contacts' => $contacts,
            'messages' => $messages,
        ]);
    }

    public function sendWhatsapp(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $contactIds = array_map('intval', $_POST['contact_ids'] ?? []);
        $text = trim($_POST['message_text'] ?? '');

        if (empty($contactIds) || $text === '') {
            $_SESSION['flash_error'] = 'Selecione ao menos um contato e escreva uma mensagem.';
            header('Location: /promoter/events/' . $eventId . '/whatsapp');
            return;
        }

        $count = WhatsappMessage::createForContacts((int) $eventId, $contactIds, $text);
        $_SESSION['flash_success'] = "Mensagem registrada para {$count} contato(s). Integração com o provedor de WhatsApp ainda não está conectada — este é o registro/fila que será usado quando ela for ativada.";
        header('Location: /promoter/events/' . $eventId . '/whatsapp');
    }

    public function calls(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $contacts = Contact::forEvent((int) $eventId);
        $calls = CallLog::forEvent((int) $eventId);

        View::render('promoter/calls', [
            'pageTitle' => 'Ligações — ' . $event['name'],
            'event' => $event,
            'contacts' => $contacts,
            'calls' => $calls,
        ]);
    }

    public function createCall(string $eventId): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        $contactId = (int) ($_POST['contact_id'] ?? 0);
        $contact = Contact::find($contactId);

        if (!$contact || (int) $contact['event_id'] !== (int) $eventId) {
            $_SESSION['flash_error'] = 'Contato inválido.';
            header('Location: /promoter/events/' . $eventId . '/calls');
            return;
        }

        CallLog::create((int) $eventId, $contactId, $_POST['outcome'] ?? 'scheduled', trim($_POST['notes'] ?? ''));
        $_SESSION['flash_success'] = 'Registro de ligação salvo.';
        header('Location: /promoter/events/' . $eventId . '/calls');
    }
}
