<?php

use App\Csrf;
use App\View;

$statusLabels = [
    'pending' => 'Pendente', 'sent' => 'Enviado', 'failed' => 'Falhou',
    'opened' => 'Aberto', 'clicked' => 'Clicou',
];
?>
<p><a href="/promoter/events/<?= (int) $event['id'] ?>/campaigns">&larr; Voltar às campanhas</a></p>
<h1><?= View::e($campaign['name']) ?></h1>

<div class="stats-grid">
    <div class="stat-tile"><div class="value"><?= (int) $stats['total'] ?></div><div class="label">Total</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['pending'] ?></div><div class="label">Na fila</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['sent'] ?></div><div class="label">Enviados</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['opened'] ?></div><div class="label">Abertos</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['clicked'] ?></div><div class="label">Clicaram</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['failed'] ?></div><div class="label">Falhas</div></div>
</div>

<div class="card">
    <p>Status atual: <strong><?= View::e($campaign['status']) ?></strong></p>
    <div class="actions">
        <?php if (in_array($campaign['status'], ['draft', 'paused'], true)): ?>
            <form method="post" action="/promoter/campaigns/<?= (int) $campaign['id'] ?>/send">
                <?= Csrf::field() ?>
                <button type="submit" class="btn">Enviar agora (respeitando limite/hora)</button>
            </form>
        <?php elseif (in_array($campaign['status'], ['queued', 'sending'], true)): ?>
            <form method="post" action="/promoter/campaigns/<?= (int) $campaign['id'] ?>/pause">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-secondary">Pausar envio</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Destinatários</h2>
    <table>
        <thead><tr><th>Contato</th><th>E-mail</th><th>Status</th><th>Enviado em</th><th>Aberturas</th><th>Cliques</th></tr></thead>
        <tbody>
        <?php foreach ($recipients as $r): ?>
            <tr>
                <td><?= View::e($r['contact_name']) ?></td>
                <td><?= View::e($r['contact_email']) ?></td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= $statusLabels[$r['status']] ?? $r['status'] ?></span></td>
                <td><?= $r['sent_at'] ? View::e(date('d/m/Y H:i', strtotime($r['sent_at']))) : '—' ?></td>
                <td><?= (int) $r['open_count'] ?></td>
                <td><?= (int) $r['click_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
