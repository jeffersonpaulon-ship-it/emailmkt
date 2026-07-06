<?php

use App\Csrf;
use App\Url;
use App\View;

$eventBase = '/promoter/events/' . (int) $event['id'];
?>
<p><a href="<?= Url::to($eventBase) ?>">&larr; Voltar ao evento</a></p>
<h1>Visibilidade do cliente — <?= View::e($event['name']) ?></h1>
<p class="text-muted">Escolha o que cada cliente pode ver sobre este evento. Se nenhuma opção for marcada, o cliente vê apenas que o evento existe.</p>

<?php if (empty($clients)): ?>
    <div class="card"><p class="text-muted">Você ainda não tem clientes cadastrados. Peça ao administrador para criar um usuário do tipo "Cliente" vinculado a você.</p></div>
<?php else: ?>
    <?php foreach ($clients as $client): ?>
        <?php $v = $visibility[(int) $client['id']] ?? null; ?>
        <div class="card">
            <h2><?= View::e($client['name']) ?> <span class="text-muted">(<?= View::e($client['email']) ?>)</span></h2>
            <form method="post" action="<?= Url::to($eventBase . '/visibility') ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">

                <div class="checkbox-row">
                    <input type="checkbox" id="contacts-<?= (int) $client['id'] ?>" name="can_view_contacts" <?= (!$v || $v['can_view_contacts']) ? 'checked' : '' ?>>
                    <label for="contacts-<?= (int) $client['id'] ?>" style="margin:0">Ver lista de contatos e status de RSVP</label>
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" id="email-<?= (int) $client['id'] ?>" name="can_view_email_stats" <?= (!$v || $v['can_view_email_stats']) ? 'checked' : '' ?>>
                    <label for="email-<?= (int) $client['id'] ?>" style="margin:0">Ver estatísticas de e-mail (aberturas/cliques)</label>
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" id="whatsapp-<?= (int) $client['id'] ?>" name="can_view_whatsapp" <?= ($v && $v['can_view_whatsapp']) ? 'checked' : '' ?>>
                    <label for="whatsapp-<?= (int) $client['id'] ?>" style="margin:0">Ver mensagens de WhatsApp</label>
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" id="calls-<?= (int) $client['id'] ?>" name="can_view_calls" <?= ($v && $v['can_view_calls']) ? 'checked' : '' ?>>
                    <label for="calls-<?= (int) $client['id'] ?>" style="margin:0">Ver histórico de ligações</label>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Salvar</button>
                    <?php if ($v): ?>
                        <button type="submit" name="revoke" value="1" class="btn btn-danger" onclick="return confirm('Remover todo o acesso deste cliente a este evento?');">Remover acesso ao evento</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
