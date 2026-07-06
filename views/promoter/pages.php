<?php

use App\Csrf;
use App\View;

/** @var array|null $confirmation */
/** @var array|null $capture */

function page_field(?array $page, string $field, string $default = ''): string
{
    return $page[$field] ?? $default;
}
?>
<p><a href="/promoter/events/<?= (int) $event['id'] ?>">&larr; Voltar ao evento</a></p>
<h1>Páginas públicas — <?= View::e($event['name']) ?></h1>

<div class="card">
    <h2>Página de confirmação (RSVP)</h2>
    <p class="text-muted">Usada para os convidados já cadastrados confirmarem presença.</p>
    <?php if ($confirmation): ?>
        <p>URL: <a href="<?= View::e($appUrl) ?>/rsvp/<?= View::e($confirmation['slug']) ?>" target="_blank"><?= View::e($appUrl) ?>/rsvp/<?= View::e($confirmation['slug']) ?></a></p>
    <?php endif; ?>
    <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/pages/confirmation">
        <?= Csrf::field() ?>
        <label for="c_slug">Slug</label>
        <input type="text" id="c_slug" name="slug" value="<?= View::e(page_field($confirmation, 'slug', 'rsvp-' . $event['slug'])) ?>">

        <label for="c_title">Título</label>
        <input type="text" id="c_title" name="title" value="<?= View::e(page_field($confirmation, 'title', 'Confirme sua presença')) ?>" required>

        <label for="c_intro">Texto de introdução</label>
        <textarea id="c_intro" name="intro_text"><?= View::e(page_field($confirmation, 'intro_text')) ?></textarea>

        <label for="c_success">Mensagem de sucesso</label>
        <textarea id="c_success" name="success_message"><?= View::e(page_field($confirmation, 'success_message', 'Presença confirmada! Obrigado.')) ?></textarea>

        <div class="checkbox-row">
            <input type="checkbox" id="c_active" name="is_active" <?= (!$confirmation || $confirmation['is_active']) ? 'checked' : '' ?>>
            <label for="c_active" style="margin:0">Página ativa</label>
        </div>

        <button type="submit" class="btn">Salvar página de confirmação</button>
    </form>
</div>

<div class="card">
    <h2>Página de captura (leads)</h2>
    <p class="text-muted">Usada para capturar novos contatos que ainda não estão na sua lista.</p>
    <?php if ($capture): ?>
        <p>URL: <a href="<?= View::e($appUrl) ?>/captura/<?= View::e($capture['slug']) ?>" target="_blank"><?= View::e($appUrl) ?>/captura/<?= View::e($capture['slug']) ?></a></p>
    <?php endif; ?>
    <form method="post" action="/promoter/events/<?= (int) $event['id'] ?>/pages/capture">
        <?= Csrf::field() ?>
        <label for="p_slug">Slug</label>
        <input type="text" id="p_slug" name="slug" value="<?= View::e(page_field($capture, 'slug', 'captura-' . $event['slug'])) ?>">

        <label for="p_title">Título</label>
        <input type="text" id="p_title" name="title" value="<?= View::e(page_field($capture, 'title', 'Garanta seu convite')) ?>" required>

        <label for="p_intro">Texto de introdução</label>
        <textarea id="p_intro" name="intro_text"><?= View::e(page_field($capture, 'intro_text')) ?></textarea>

        <label for="p_success">Mensagem de sucesso</label>
        <textarea id="p_success" name="success_message"><?= View::e(page_field($capture, 'success_message', 'Recebemos seu cadastro!')) ?></textarea>

        <div class="checkbox-row">
            <input type="checkbox" id="p_active" name="is_active" <?= (!$capture || $capture['is_active']) ? 'checked' : '' ?>>
            <label for="p_active" style="margin:0">Página ativa</label>
        </div>

        <button type="submit" class="btn">Salvar página de captura</button>
    </form>
</div>
