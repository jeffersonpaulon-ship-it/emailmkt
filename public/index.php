<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/autoload.php';

session_start();

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CampaignController;
use App\Controllers\ClientController;
use App\Controllers\ContactController;
use App\Controllers\EventController;
use App\Controllers\MessagingController;
use App\Controllers\PageController;
use App\Controllers\PublicController;
use App\Controllers\TemplateController;
use App\Controllers\TrackingController;
use App\Controllers\VisibilityController;
use App\Auth;
use App\Router;
use App\Url;

$router = new Router();

// Autenticação
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/', function () {
    if (!Auth::check()) {
        header('Location: ' . Url::to('/login'));
        return;
    }

    $target = match (Auth::role()) {
        'admin' => '/admin/users',
        'promoter' => '/promoter/events',
        'client' => '/client/events',
        default => '/login',
    };
    header('Location: ' . Url::to($target));
});

// Admin
$router->get('/admin/users', [AdminController::class, 'index']);
$router->post('/admin/users', [AdminController::class, 'create']);
$router->post('/admin/users/{id}/rate-limit', [AdminController::class, 'updateRateLimit']);
$router->post('/admin/users/{id}/status', [AdminController::class, 'updateStatus']);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'delete']);

// Promotor — eventos
$router->get('/promoter/events', [EventController::class, 'index']);
$router->post('/promoter/events', [EventController::class, 'create']);
$router->get('/promoter/events/{id}', [EventController::class, 'show']);
$router->post('/promoter/events/{id}', [EventController::class, 'edit']);
$router->post('/promoter/events/{id}/delete', [EventController::class, 'delete']);

// Promotor — contatos
$router->get('/promoter/events/{eventId}/contacts', [ContactController::class, 'index']);
$router->post('/promoter/events/{eventId}/contacts', [ContactController::class, 'create']);
$router->post('/promoter/events/{eventId}/contacts/{contactId}/delete', [ContactController::class, 'delete']);

// Promotor — páginas públicas
$router->get('/promoter/events/{eventId}/pages', [PageController::class, 'index']);
$router->post('/promoter/events/{eventId}/pages/{type}', [PageController::class, 'save']);

// Promotor — templates
$router->get('/promoter/events/{eventId}/templates', [TemplateController::class, 'index']);
$router->post('/promoter/events/{eventId}/templates', [TemplateController::class, 'create']);
$router->post('/promoter/events/{eventId}/templates/{templateId}/delete', [TemplateController::class, 'delete']);

// Promotor — campanhas
$router->get('/promoter/events/{eventId}/campaigns', [CampaignController::class, 'index']);
$router->post('/promoter/events/{eventId}/campaigns', [CampaignController::class, 'create']);
$router->get('/promoter/campaigns/{campaignId}', [CampaignController::class, 'show']);
$router->post('/promoter/campaigns/{campaignId}/send', [CampaignController::class, 'send']);
$router->post('/promoter/campaigns/{campaignId}/pause', [CampaignController::class, 'pause']);

// Promotor — WhatsApp e ligações (estrutura, sem integração real)
$router->get('/promoter/events/{eventId}/whatsapp', [MessagingController::class, 'whatsapp']);
$router->post('/promoter/events/{eventId}/whatsapp/send', [MessagingController::class, 'sendWhatsapp']);
$router->get('/promoter/events/{eventId}/calls', [MessagingController::class, 'calls']);
$router->post('/promoter/events/{eventId}/calls', [MessagingController::class, 'createCall']);

// Promotor — visibilidade do cliente
$router->get('/promoter/events/{eventId}/visibility', [VisibilityController::class, 'index']);
$router->post('/promoter/events/{eventId}/visibility', [VisibilityController::class, 'update']);

// Cliente
$router->get('/client/events', [ClientController::class, 'index']);
$router->get('/client/events/{eventId}', [ClientController::class, 'show']);

// Páginas públicas (confirmação de presença / captura de leads)
// Nota: usa "/confirmar/" (não "/rsvp/") para não colidir com o nome do produto
// quando o app é hospedado numa subpasta chamada "rsvp" (ex: seusite.com/rsvp/).
$router->get('/confirmar/{slug}', [PublicController::class, 'confirmation']);
$router->post('/confirmar/{slug}', [PublicController::class, 'confirmationSubmit']);
$router->get('/captura/{slug}', [PublicController::class, 'capture']);
$router->post('/captura/{slug}', [PublicController::class, 'captureSubmit']);

// Tracking de e-mail
$router->get('/track/open/{token}.gif', [TrackingController::class, 'pixel']);
$router->get('/track/click/{token}', [TrackingController::class, 'click']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
