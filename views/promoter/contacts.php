<?php

use App\Csrf;
use App\View;

$statusLabels = ['pending' => 'Pendente', 'confirmed' => 'Confirmado', 'declined' => 'Recusado'];
$sourceLabels = ['manual' => 'Manual', 'import' => 'Importado', 'capture_page' => 'Pág. de captura', 'confirmation_page' => 'Pág. de confirmação'];
?>
<p><a href="/promoter/events/<?= (int) $event['id'] ?>">&larr; Voltar ao evento</a></p>
<h1>Contatos — <?= View::e($event['name']) ?></h1>

<div class="card">
    <h2>Adicionar contato</h2>
    <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/contacts">
        <?= Csrf::field() ?>
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Telefone</label>
        <input type="tel" id="phone" name="phone">

        <button type="submit" class="btn">Adicionar</button>
    </form>
</div>

<div class="card">
    <h2>Lista de contatos (<?= count($contacts) ?>)</h2>
    <?php if (empty($contacts)): ?>
        <p class="text-muted">Nenhum contato cadastrado ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>RSVP</th><th>Origem</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($contacts as $c): ?>
                <tr>
                    <td><?= View::e($c['name']) ?></td>
                    <td><?= View::e($c['email']) ?></td>
                    <td><?= View::e($c['phone'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $c['rsvp_status'] ?>"><?= $statusLabels[$c['rsvp_status']] ?? $c['rsvp_status'] ?></span></td>
                    <td><?= $sourceLabels[$c['source']] ?? $c['source'] ?></td>
                    <td>
                        <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/contacts/<?= (int) $c['id'] ?>/delete" class="inline" onsubmit="return confirm('Remover este contato?');">
                            <?= Csrf::field() ?>
                            <button type="submit" class="link-button">remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
