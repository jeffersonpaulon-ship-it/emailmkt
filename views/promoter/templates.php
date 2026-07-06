<?php

use App\Csrf;
use App\Url;
use App\View;

$eventBase = '/promoter/events/' . (int) $event['id'];
?>
<p><a href="<?= Url::to($eventBase) ?>">&larr; Voltar ao evento</a></p>
<h1>Templates de e-mail — <?= View::e($event['name']) ?></h1>

<div class="card">
    <h2>Novo template</h2>
    <p class="text-muted">Variáveis disponíveis: <code>{{nome}}</code>, <code>{{evento}}</code>, <code>{{link_confirmacao}}</code></p>
    <form method="post" action="<?= Url::to($eventBase . '/templates') ?>">
        <?= Csrf::field() ?>
        <label for="name">Nome do template</label>
        <input type="text" id="name" name="name" required>

        <label for="subject">Assunto</label>
        <input type="text" id="subject" name="subject" required>

        <label for="body_html">Corpo (HTML)</label>
        <textarea id="body_html" name="body_html" required><?= View::e("<p>Olá {{nome}},</p>\n<p>Você está convidado para {{evento}}!</p>\n<p><a href=\"{{link_confirmacao}}\">Confirme sua presença aqui</a></p>") ?></textarea>

        <button type="submit" class="btn">Criar template</button>
    </form>
</div>

<div class="card">
    <h2>Templates existentes</h2>
    <?php if (empty($templates)): ?>
        <p class="text-muted">Nenhum template criado ainda.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th>Assunto</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($templates as $t): ?>
                <tr>
                    <td><?= View::e($t['name']) ?></td>
                    <td><?= View::e($t['subject']) ?></td>
                    <td>
                        <form method="post" action="<?= Url::to($eventBase . '/templates/' . (int) $t['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Remover este template?');">
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
