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

        <form method="post" action="<?= Url::to('/captura/' . $page['slug']) ?>">
            <?= Csrf::field() ?>

            <label for="name">Nome</label>
            <input type="text" id="name" name="name" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Telefone</label>
            <input type="tel" id="phone" name="phone">

            <button type="submit" class="btn">Enviar</button>
        </form>
    </div>
</main>
</body>
</html>
