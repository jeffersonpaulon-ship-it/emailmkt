<?php

use App\Csrf;
use App\View;

$statusLabels = ['draft' => 'Rascunho', 'active' => 'Ativo', 'closed' => 'Encerrado'];
?>
<p><a href="/promoter/events">&larr; Voltar aos eventos</a></p>
<h1><?= View::e($event['name']) ?></h1>

<div class="stats-grid">
    <div class="stat-tile"><div class="value"><?= (int) $stats['total'] ?></div><div class="label">Contatos</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['confirmed'] ?></div><div class="label">Confirmados</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['declined'] ?></div><div class="label">Recusados</div></div>
    <div class="stat-tile"><div class="value"><?= (int) $stats['pending'] ?></div><div class="label">Pendentes</div></div>
</div>

<div class="card">
    <h2>Editar evento</h2>
    <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>">
        <?= Csrf::field() ?>
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" value="<?= View::e($event['name']) ?>" required>

        <label for="description">Descrição</label>
        <textarea id="description" name="description"><?= View::e($event['description']) ?></textarea>

        <label for="event_date">Data/hora</label>
        <input type="datetime-local" id="event_date" name="event_date" value="<?= $event['event_date'] ? View::e(date('Y-m-d\TH:i', strtotime($event['event_date']))) : '' ?>">

        <label for="location">Local</label>
        <input type="text" id="location" name="location" value="<?= View::e($event['location']) ?>">

        <label for="slug">Slug (usado nas URLs públicas)</label>
        <input type="text" id="slug" name="slug" value="<?= View::e($event['slug']) ?>">

        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach ($statusLabels as $value => $label): ?>
                <option value="<?= $value ?>" <?= $event['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Salvar alterações</button>
    </form>
</div>

<div class="card">
    <h2>Gerenciar</h2>
    <p>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/contacts">Contatos</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/pages">Páginas públicas</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/templates">Templates de e-mail</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/campaigns">Campanhas de e-mail</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/whatsapp">WhatsApp</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/calls">Ligações</a>
        <a class="btn" href="/promoter/events/<?= (int) $event['id'] ?>/visibility">Visibilidade do cliente</a>
    </p>
</div>

<div class="card">
    <h2>Campanhas recentes</h2>
    <?php if (empty($campaigns)): ?>
        <p class="text-muted">Nenhuma campanha criada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($campaigns as $c): ?>
                <tr>
                    <td><?= View::e($c['name']) ?></td>
                    <td><?= View::e($c['status']) ?></td>
                    <td><a href="/promoter/campaigns/<?= (int) $c['id'] ?>">ver estatísticas</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Clientes com acesso a este evento</h2>
    <?php if (empty($clients)): ?>
        <p class="text-muted">Nenhum cliente com acesso configurado. <a href="/promoter/events/<?= (int) $event['id'] ?>/visibility">Configurar visibilidade</a>.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($clients as $c): ?>
                <li><?= View::e($c['client_name']) ?> (<?= View::e($c['client_email']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
