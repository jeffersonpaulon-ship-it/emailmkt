<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Visibility;
use App\Url;
use App\View;

final class EventController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(): void
    {
        $events = Event::forPromoter(Auth::id());
        View::render('promoter/events', ['pageTitle' => 'Eventos', 'events' => $events]);
    }

    public function create(): void
    {
        Csrf::requireValid();

        $slug = $this->uniqueSlug(trim($_POST['name'] ?? 'evento'));

        $id = Event::create([
            'promoter_id' => Auth::id(),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'location' => trim($_POST['location'] ?? ''),
            'slug' => $slug,
            'status' => 'draft',
        ]);

        $_SESSION['flash_success'] = 'Evento criado com sucesso.';
        header('Location: ' . Url::to('/promoter/events/' . $id));
    }

    public function show(string $id): void
    {
        $event = $this->ownedEventOrFail($id);
        $stats = Contact::stats((int) $id);
        $campaigns = Campaign::forEvent((int) $id);
        $clients = Visibility::forEvent((int) $id);

        View::render('promoter/event_detail', [
            'pageTitle' => $event['name'],
            'event' => $event,
            'stats' => $stats,
            'campaigns' => $campaigns,
            'clients' => $clients,
        ]);
    }

    public function edit(string $id): void
    {
        $event = $this->ownedEventOrFail($id);

        Csrf::requireValid();

        $slug = trim($_POST['slug'] ?? $event['slug']);
        if ($slug !== $event['slug'] && Event::slugExists($slug, (int) $id)) {
            $slug = $this->uniqueSlug($slug);
        }

        Event::update((int) $id, [
            'name' => trim($_POST['name'] ?? $event['name']),
            'description' => trim($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'location' => trim($_POST['location'] ?? ''),
            'slug' => $slug,
            'status' => $_POST['status'] ?? $event['status'],
        ]);

        $_SESSION['flash_success'] = 'Evento atualizado.';
        header('Location: ' . Url::to('/promoter/events/' . $id));
    }

    public function delete(string $id): void
    {
        $this->ownedEventOrFail($id);
        Csrf::requireValid();
        Event::delete((int) $id);
        $_SESSION['flash_success'] = 'Evento removido.';
        header('Location: ' . Url::to('/promoter/events'));
    }

    private function uniqueSlug(string $name): string
    {
        $base = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($name))), '-');
        $base = $base !== '' ? $base : 'evento';
        $slug = $base;
        $suffix = 1;

        while (Event::slugExists($slug)) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }
}
