<?php

use App\Csrf;
use App\Url;
use App\View;

$statusLabels = ['draft' => 'Rascunho', 'active' => 'Ativo', 'closed' => 'Encerrado'];
?>
<h1>Meus eventos</h1>

<div class="card">
    <h2>Novo evento</h2>
    <form method="post" action="<?= Url::to('/promoter/events') ?>">
        <?= Csrf::field() ?>
        <label for="name">Nome do evento</label>
        <input type="text" id="name" name="name" required>

        <label for="description">Descrição</label>
        <textarea id="description" name="description"></textarea>

        <label for="event_date">Data/hora</label>
        <input type="datetime-local" id="event_date" name="event_date">

        <label for="location">Local</label>
        <input type="text" id="location" name="location">

        <button type="submit" class="btn">Criar evento</button>
    </form>
</div>

<div class="card">
    <h2>Eventos</h2>
    <?php if (empty($events)): ?>
        <p class="text-muted">Nenhum evento criado ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>Data</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><a href="<?= Url::to('/promoter/events/' . (int) $e['id']) ?>"><?= View::e($e['name']) ?></a></td>
                    <td><?= $e['event_date'] ? View::e(date('d/m/Y H:i', strtotime($e['event_date']))) : '—' ?></td>
                    <td><?= $statusLabels[$e['status']] ?? $e['status'] ?></td>
                    <td><a href="<?= Url::to('/promoter/events/' . (int) $e['id']) ?>">gerenciar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
