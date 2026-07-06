<?php

use App\Csrf;
use App\View;
?>
<p><a href="/promoter/events/<?= (int) $event['id'] ?>">&larr; Voltar ao evento</a></p>
<h1>WhatsApp — <?= View::e($event['name']) ?></h1>
<p class="text-muted">Integração com provedor de WhatsApp ainda não conectada nesta etapa — a estrutura de fila e histórico já está pronta para quando a integração for ativada.</p>

<div class="card">
    <h2>Enviar mensagem</h2>
    <?php if (empty($contacts)): ?>
        <p class="text-muted">Cadastre contatos neste evento antes de enviar mensagens.</p>
    <?php else: ?>
        <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/whatsapp/send">
            <?= Csrf::field() ?>
            <label>Contatos</label>
            <div class="card" style="max-height:220px; overflow-y:auto;">
                <?php foreach ($contacts as $c): ?>
                    <div class="checkbox-row">
                        <input type="checkbox" id="contact-<?= (int) $c['id'] ?>" name="contact_ids[]" value="<?= (int) $c['id'] ?>">
                        <label for="contact-<?= (int) $c['id'] ?>" style="margin:0"><?= View::e($c['name']) ?> (<?= View::e($c['phone'] ?? 'sem telefone') ?>)</label>
                    </div>
                <?php endforeach; ?>
            </div>

            <label for="message_text">Mensagem</label>
            <textarea id="message_text" name="message_text" required></textarea>

            <button type="submit" class="btn">Registrar envio</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Histórico</h2>
    <?php if (empty($messages)): ?>
        <p class="text-muted">Nenhuma mensagem registrada ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Contato</th><th>Telefone</th><th>Mensagem</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $m): ?>
                <tr>
                    <td><?= View::e($m['contact_name']) ?></td>
                    <td><?= View::e($m['contact_phone'] ?? '—') ?></td>
                    <td><?= View::e($m['message_text']) ?></td>
                    <td><?= View::e($m['status']) ?></td>
                    <td><?= $m['sent_at'] ? View::e(date('d/m/Y H:i', strtotime($m['sent_at']))) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
