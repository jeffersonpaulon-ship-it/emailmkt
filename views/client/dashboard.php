<?php
declare(strict_types=1);

// Exemplo de dados vindos do EventModel para o evento ativo
$metrics = $metrics ?? [
    'guests' => ['total' => 0, 'confirmed' => 0, 'declined' => 0, 'pending' => 0, 'total_tickets' => 0],
    'email_engagement' => ['total_opens' => 0, 'total_clicks' => 0]
];
$eventTitle = $eventTitle ?? "Carregando evento...";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Cliente - RSVP</title>
    <link rel="stylesheet" href="https://wnebr.com/rsvp/public/assets/css/style.css">
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <h2>RSVP Cliente</h2>
        <nav>
            <p><strong>Evento Ativo:</strong><br><?= htmlspecialchars($eventTitle) ?></p>
        </nav>
    </aside>

    <main class="main-content">
        <header style="margin-bottom: 2rem;">
            <h1>Dashboard do Evento</h1>
            <p class="text-muted">Acompanhe em tempo real os números de confirmação de presença.</p>
        </header>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-title">Total de Convidados</div>
                <div class="card-value"><?= $metrics['guests']['total'] ?></div>
            </div>
            <div class="card">
                <div class="card-title">Ingressos Previstos</div>
                <div class="card-value"><?= $metrics['guests']['total_tickets'] ?></div>
            </div>
            <div class="card" style="border-left: 4px solid var(--color-confirmed);">
                <div class="card-title" style="color: var(--color-confirmed);">Confirmados</div>
                <div class="card-value"><?= $metrics['guests']['confirmed'] ?></div>
            </div>
            <div class="card" style="border-left: 4px solid var(--color-declined);">
                <div class="card-title" style="color: var(--color-declined);">Recusados</div>
                <div class="card-value"><?= $metrics['guests']['declined'] ?></div>
            </div>
            <div class="card" style="border-left: 4px solid var(--color-pending);">
                <div class="card-title" style="color: var(--color-pending);">Pendentes</div>
                <div class="card-value"><?= $metrics['guests']['pending'] ?></div>
            </div>
        </div>

        <h2>Engajamento dos Convites (E-mail)</h2>
        <div class="dashboard-grid" style="margin-top: 1rem;">
            <div class="card">
                <div class="card-title">Aberturas de E-mail</div>
                <div class="card-value" style="color: var(--primary-color);"><?= $metrics['email_engagement']['total_opens'] ?></div>
            </div>
            <div class="card">
                <div class="card-title">Cliques em Links</div>
                <div class="card-value" style="color: var(--primary-color);"><?= $metrics['email_engagement']['total_clicks'] ?></div>
            </div>
        </div>
    </main>
</div>

</body>
</html>