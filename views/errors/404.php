<?php use App\Url; ?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>404 — Não encontrado</title><link rel="stylesheet" href="<?= Url::to('/assets/style.css') ?>"></head>
<body>
<main class="container">
    <h1>404</h1>
    <p>A página que você procura não existe.</p>
    <a href="<?= Url::to('/') ?>">Voltar ao início</a>
</main>
</body>
</html>
