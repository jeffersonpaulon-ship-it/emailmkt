<?php

use App\Csrf;
use App\View;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar — RSVP Manager</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<main class="container login-box">
    <h1>RSVP Manager</h1>
    <div class="card">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-error"><?= View::e($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        <form method="post" action="/login">
            <?= Csrf::field() ?>
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</main>
</body>
</html>
