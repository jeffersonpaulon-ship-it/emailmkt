<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Controllers\Concerns\EventOwnership;
use App\Csrf;
use App\Models\Page;
use App\View;

final class PageController
{
    use EventOwnership;

    public function __construct()
    {
        Auth::requireRole('promoter');
    }

    public function index(string $eventId): void
    {
        $event = $this->ownedEventOrFail($eventId);
        $confirmation = Page::findByEventAndType((int) $eventId, 'confirmation');
        $capture = Page::findByEventAndType((int) $eventId, 'capture');
        $appUrl = Config::get('app.url');

        View::render('promoter/pages', [
            'pageTitle' => 'Páginas públicas — ' . $event['name'],
            'event' => $event,
            'confirmation' => $confirmation,
            'capture' => $capture,
            'appUrl' => $appUrl,
        ]);
    }

    public function save(string $eventId, string $type): void
    {
        $this->ownedEventOrFail($eventId);
        Csrf::requireValid();

        if (!in_array($type, ['confirmation', 'capture'], true)) {
            http_response_code(404);
            return;
        }

        $slug = trim($_POST['slug'] ?? '');
        $slug = $slug !== '' ? $slug : ($type . '-' . $eventId);

        $existing = Page::findByEventAndType((int) $eventId, $type);
        $existingId = $existing['id'] ?? null;

        if (Page::slugExists($slug, $existingId)) {
            $slug .= '-' . substr(md5((string) random_int(0, PHP_INT_MAX)), 0, 5);
        }

        Page::upsert((int) $eventId, $type, [
            'slug' => $slug,
            'title' => trim($_POST['title'] ?? ''),
            'intro_text' => trim($_POST['intro_text'] ?? ''),
            'success_message' => trim($_POST['success_message'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);

        $_SESSION['flash_success'] = 'Página atualizada.';
        header('Location: /promoter/events/' . $eventId . '/pages');
    }
}
