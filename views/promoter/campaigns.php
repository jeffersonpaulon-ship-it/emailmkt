<?php

use App\Csrf;
use App\View;

$statusLabels = [
    'draft' => 'Rascunho', 'queued' => 'Na fila', 'sending' => 'Enviando',
    'completed' => 'Concluída', 'paused' => 'Pausada',
];
?>
<p><a href="/promoter/events/<?= (int) $event['id'] ?>">&larr; Voltar ao evento</a></p>
<h1>Campanhas de e-mail — <?= View::e($event['name']) ?></h1>

<div class="card">
    <h2>Nova campanha</h2>
    <?php if (empty($templates)): ?>
        <p class="text-muted">Você precisa <a href="/promoter/events/<?= (int) $event['id'] ?>/templates">criar um template</a> antes de disparar uma campanha.</p>
    <?php else: ?>
        <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/campaigns">
            <?= Csrf::field() ?>
            <label for="name">Nome da campanha</label>
            <input type="text" id="name" name="name" required>

            <label for="template_id">Template</label>
            <select id="template_id" name="template_id" required>
                <?php foreach ($templates as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= View::e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="audience">Público</label>
            <select id="audience" name="audience">
                <option value="all">Todos os contatos</option>
                <option value="pending">Apenas RSVP pendente</option>
            </select>

            <button type="submit" class="btn">Criar e enfileirar destinatários</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Campanhas</h2>
    <?php if (empty($campaigns)): ?>
        <p class="text-muted">Nenhuma campanha criada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($campaigns as $c): ?>
                <tr>
                    <td><?= View::e($c['name']) ?></td>
                    <td><?= $statusLabels[$c['status']] ?? $c['status'] ?></td>
                    <td><a href="/promoter/campaigns/<?= (int) $c['id'] ?>">ver detalhes</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
