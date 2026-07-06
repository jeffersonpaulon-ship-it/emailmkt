<?php

use App\Csrf;
use App\Url;
use App\View;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($page['title']) ?></title>
<link rel="stylesheet" href="<?= Url::to('/assets/style.css') ?>">
</head>
<body>
<main class="public-page">
    <div class="card">
        <h1><?= View::e($page['title']) ?></h1>
        <?php if (!empty($page['intro_text'])): ?><p><?= nl2br(View::e($page['intro_text'])) ?></p><?php endif; ?>

        <form method="post" action="<?= Url::to('/confirmar/' . $page['slug']) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="t" value="<?= View::e($token) ?>">

            <label for="name">Nome</label>
            <input type="text" id="name" name="name" value="<?= View::e($prefill['name']) ?>" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= View::e($prefill['email']) ?>" required>

            <label for="phone">Telefone</label>
            <input type="tel" id="phone" name="phone" value="<?= View::e($prefill['phone']) ?>">

            <label for="attending">Você vai comparecer?</label>
            <select id="attending" name="attending">
                <option value="yes">Sim, vou comparecer</option>
                <option value="no">Não vou poder ir</option>
            </select>

            <label for="guests_count">Número de acompanhantes</label>
            <input type="number" id="guests_count" name="guests_count" value="<?= (int) $prefill['guests_count'] ?>" min="0">

            <button type="submit" class="btn">Confirmar</button>
        </form>
    </div>
</main>
</body>
</html>
