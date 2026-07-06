<?php

use App\Url;
use App\View;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= Url::to('/assets/style.css') ?>">
</head>
<body>
<main class="public-page">
    <div class="card">
        <h1>Obrigado!</h1>
        <p><?= View::e($message) ?></p>
    </div>
</main>
</body>
</html>
