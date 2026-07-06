<?php
declare(strict_types=1);

$eventId = $activeEventId ?? 1; // ID do evento contextualizado
$guests = $guestsList ?? [];    // Array vindo do GuestModel::getAllByEvent()
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Promotor - Gestão RSVP</title>
    <link rel="stylesheet" href="https://wnebr.com/rsvp/public/assets/css/style.css">
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <h2>Promotor RSVP</h2>
        <p>Menu de Campanhas</p>
        <p>Modelos de Email</p>
        <p>Régua de Disparos</p>
    </aside>

    <main class="main-content">
        <section class="card" style="margin-bottom: 2rem;">
            <h3>Importar Listagem do Excel (.CSV)</h3>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
                Selecione o arquivo com as colunas padrão: Categoria, Ingressos, Email, Nome Completo, WhatsApp...
            </p>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="file" id="excel_file_input" class="form-control" accept=".csv" style="max-width: 400px;">
                <button class="btn btn-primary" onclick="RsvpApp.importExcel(<?= $eventId ?>, document.getElementById('excel_file_input'))">
                    Processar Lista via Ajax
                </button>
            </div>
        </section>

        <section>
            <h3 style="margin-bottom: 1rem;">Lista de Convidados do Evento</h3>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nome Completo</th>
                            <th>Empresa / Cargo</th>
                            <th>WhatsApp</th>
                            <th>Canal</th>
                            <th>Status RSVP</th>
                            <th>Ações Manuais (Call / Whats)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guests)): ?>
                            <tr><td colspan="6" style="text-align:center;">Nenhum convidado importado para este evento.</td></tr>
                        <?php else: ?>
                            <?php foreach ($guests as $guest): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($guest['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($guest['company'] ?? '-') ?> <br> <small><?= htmlspecialchars($guest['job_title'] ?? '') ?></small></td>
                                    <td><?= htmlspecialchars($guest['whatsapp'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($guest['contact_method'] ?? 'Não definido') ?></td>
                                    <td>
                                        <span id="badge-guest-<?= $guest['id'] ?>" class="badge badge-<?= $guest['confirmation_status'] ?>">
                                            <?= $guest['confirmation_status'] === 'confirmed' ? 'Confirmado' : ($guest['confirmation_status'] === 'declined' ? 'Recusado' : 'Pendente') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn" style="background-color: #d1fae5; color: var(--color-confirmed); padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                onclick="RsvpApp.updateGuestStatus(<?= $guest['id'] ?>, 'confirmed')">Confirmar</button>
                                        <button class="btn" style="background-color: #fee2e2; color: var(--color-declined); padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                onclick="RsvpApp.updateGuestStatus(<?= $guest['id'] ?>, 'declined')">Recusar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div id="global-loader" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); justify-content:center; align-items:center; font-weight:bold; z-index:9999;">
    Processando arquivo e populando banco de dados, por favor aguarde...
</div>

<script src="https://wnebr.com/rsvp/public/assets/js/app.js"></script>
</body>
</html>