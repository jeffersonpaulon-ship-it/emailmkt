<?php

use App\Csrf;
use App\Url;
use App\View;

$roleLabels = ['admin' => 'Admin', 'promoter' => 'Promotor', 'client' => 'Cliente'];
?>
<h1>Usuários</h1>

<div class="card">
    <h2>Novo usuário</h2>
    <form method="post" action="<?= Url::to('/admin/users') ?>">
        <?= Csrf::field() ?>
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Senha</label>
        <input type="text" id="password" name="password" required>

        <label for="role">Papel</label>
        <select id="role" name="role" onchange="document.getElementById('promoter-field').style.display = this.value === 'client' ? 'block' : 'none'; document.getElementById('rate-field').style.display = this.value === 'promoter' ? 'block' : 'none';">
            <option value="promoter">Promotor</option>
            <option value="client">Cliente</option>
            <option value="admin">Admin</option>
        </select>

        <div id="promoter-field" style="display:none">
            <label for="promoter_id">Promotor responsável</label>
            <select id="promoter_id" name="promoter_id">
                <option value="">Selecione...</option>
                <?php foreach ($promoters as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= View::e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="rate-field">
            <label for="messages_per_hour">Mensagens por hora (limite)</label>
            <input type="number" id="messages_per_hour" name="messages_per_hour" value="100" min="1">
        </div>

        <button type="submit" class="btn">Criar usuário</button>
    </form>
</div>

<div class="card">
    <h2>Todos os usuários</h2>
    <table>
        <thead>
        <tr>
            <th>Nome</th><th>E-mail</th><th>Papel</th><th>Promotor</th><th>Msgs/hora</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= View::e($u['name']) ?></td>
                <td><?= View::e($u['email']) ?></td>
                <td><?= $roleLabels[$u['role']] ?? $u['role'] ?></td>
                <td>
                    <?php
                        $owner = null;
                        if ($u['promoter_id']) {
                            foreach ($promoters as $p) {
                                if ((int) $p['id'] === (int) $u['promoter_id']) { $owner = $p['name']; break; }
                            }
                        }
                        echo $owner ? View::e($owner) : '—';
                    ?>
                </td>
                <td>
                    <?php if ($u['role'] === 'promoter'): ?>
                        <form method="post" action="<?= Url::to('/admin/users/' . (int) $u['id'] . '/rate-limit') ?>" class="inline">
                            <?= Csrf::field() ?>
                            <input type="number" name="messages_per_hour" value="<?= (int) $u['messages_per_hour'] ?>" min="1" style="width:80px;display:inline-block;margin:0">
                            <button type="submit" class="link-button">salvar</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= Url::to('/admin/users/' . (int) $u['id'] . '/status') ?>" class="inline">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                        <button type="submit" class="link-button"><?= $u['status'] === 'active' ? 'Ativo' : 'Inativo' ?></button>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?= Url::to('/admin/users/' . (int) $u['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Remover este usuário?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="link-button">remover</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
