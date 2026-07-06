<?php

use App\Auth;

$user = Auth::user();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? \App\View::e($pageTitle) . ' — ' : '' ?>RSVP Manager</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
    <div class="brand">RSVP Manager</div>
    <nav>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="/admin/users">Usuários</a>
        <?php elseif ($user['role'] === 'promoter'): ?>
            <a href="/promoter/events">Eventos</a>
        <?php elseif ($user['role'] === 'client'): ?>
            <a href="/client/events">Meus Eventos</a>
        <?php endif; ?>
    </nav>
    <div class="user-box">
        <span><?= \App\View::e($user['name']) ?> (<?= \App\View::e($user['role']) ?>)</span>
        <form method="post" action="/logout" style="display:inline">
            <?= \App\Csrf::field() ?>
            <button type="submit" class="link-button">Sair</button>
        </form>
    </div>
</header>
<?php endif; ?>
<main class="container">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= \App\View::e($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?= \App\View::e($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
