<?php

use App\Csrf;
use App\Url;
use App\View;

$outcomeLabels = ['scheduled' => 'Agendada', 'completed' => 'Concluída', 'no_answer' => 'Não atendeu', 'declined' => 'Recusou'];
$eventBase = '/promoter/events/' . (int) $event['id'];
?>
<p><a href="<?= Url::to($eventBase) ?>">&larr; Voltar ao evento</a></p>
<h1>Ligações — <?= View::e($event['name']) ?></h1>
<p class="text-muted">Discagem automática ainda não conectada — use esta tela para registrar manualmente o resultado das ligações feitas.</p>

<div class="card">
    <h2>Registrar ligação</h2>
    <?php if (empty($contacts)): ?>
        <p class="text-muted">Cadastre contatos neste evento antes de registrar ligações.</p>
    <?php else: ?>
        <form method="post" action="<?= Url::to($eventBase . '/calls') ?>">
            <?= Csrf::field() ?>
            <label for="contact_id">Contato</label>
            <select id="contact_id" name="contact_id" required>
                <?php foreach ($contacts as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= View::e($c['name']) ?> (<?= View::e($c['phone'] ?? 'sem telefone') ?>)</option>
                <?php endforeach; ?>
            </select>

            <label for="outcome">Resultado</label>
            <select id="outcome" name="outcome">
                <?php foreach ($outcomeLabels as $value => $label): ?>
                    <option value="<?= $value ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <label for="notes">Observações</label>
            <textarea id="notes" name="notes"></textarea>

            <button type="submit" class="btn">Salvar</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Histórico</h2>
    <?php if (empty($calls)): ?>
        <p class="text-muted">Nenhuma ligação registrada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Contato</th><th>Telefone</th><th>Resultado</th><th>Observações</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($calls as $c): ?>
                <tr>
                    <td><?= View::e($c['contact_name']) ?></td>
                    <td><?= View::e($c['contact_phone'] ?? '—') ?></td>
                    <td><?= $outcomeLabels[$c['outcome']] ?? $c['outcome'] ?></td>
                    <td><?= View::e($c['notes']) ?></td>
                    <td><?= View::e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
