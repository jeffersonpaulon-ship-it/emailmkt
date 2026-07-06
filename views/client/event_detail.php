<?php

use App\Models\Campaign;
use App\Url;
use App\View;
?>
<p><a href="<?= Url::to('/client/events') ?>">&larr; Voltar aos meus eventos</a></p>
<h1><?= View::e($event['name']) ?></h1>
<p class="text-muted"><?= View::e($event['description'] ?? '') ?></p>

<?php if ($contactStats): ?>
<div class="card">
    <h2>Confirmações</h2>
    <div class="stats-grid">
        <div class="stat-tile"><div class="value"><?= (int) $contactStats['total'] ?></div><div class="label">Contatos</div></div>
        <div class="stat-tile"><div class="value"><?= (int) $contactStats['confirmed'] ?></div><div class="label">Confirmados</div></div>
        <div class="stat-tile"><div class="value"><?= (int) $contactStats['declined'] ?></div><div class="label">Recusados</div></div>
        <div class="stat-tile"><div class="value"><?= (int) $contactStats['pending'] ?></div><div class="label">Pendentes</div></div>
    </div>
</div>
<?php endif; ?>

<?php if ($access['can_view_email_stats']): ?>
<div class="card">
    <h2>Campanhas de e-mail</h2>
    <?php if (empty($campaigns)): ?>
        <p class="text-muted">Nenhuma campanha enviada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>Enviados</th><th>Abertos</th><th>Cliques</th></tr></thead>
            <tbody>
            <?php foreach ($campaigns as $c): $s = Campaign::stats((int) $c['id']); ?>
                <tr>
                    <td><?= View::e($c['name']) ?></td>
                    <td><?= (int) $s['sent'] ?></td>
                    <td><?= (int) $s['opened'] ?></td>
                    <td><?= (int) $s['clicked'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($access['can_view_whatsapp']): ?>
<div class="card">
    <h2>Mensagens de WhatsApp</h2>
    <?php if (empty($whatsappMessages)): ?>
        <p class="text-muted">Nenhuma mensagem registrada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Contato</th><th>Mensagem</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($whatsappMessages as $m): ?>
                <tr>
                    <td><?= View::e($m['contact_name']) ?></td>
                    <td><?= View::e($m['message_text']) ?></td>
                    <td><?= View::e($m['status']) ?></td>
                    <td><?= $m['sent_at'] ? View::e(date('d/m/Y H:i', strtotime($m['sent_at']))) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($access['can_view_calls']): ?>
<div class="card">
    <h2>Ligações</h2>
    <?php if (empty($calls)): ?>
        <p class="text-muted">Nenhuma ligação registrada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Contato</th><th>Resultado</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($calls as $c): ?>
                <tr>
                    <td><?= View::e($c['contact_name']) ?></td>
                    <td><?= View::e($c['outcome']) ?></td>
                    <td><?= View::e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>
