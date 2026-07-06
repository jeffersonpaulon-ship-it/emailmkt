<?php

use App\Url;
use App\View;
?>
<h1>Meus eventos</h1>

<?php if (empty($events)): ?>
    <div class="card"><p class="text-muted">Nenhum evento compartilhado com você ainda.</p></div>
<?php else: ?>
    <div class="card">
        <table>
            <thead><tr><th>Evento</th><th>Data</th><th>Local</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?= View::e($e['name']) ?></td>
                    <td><?= $e['event_date'] ? View::e(date('d/m/Y H:i', strtotime($e['event_date']))) : '—' ?></td>
                    <td><?= View::e($e['location'] ?? '—') ?></td>
                    <td><a href="<?= Url::to('/client/events/' . (int) $e['id']) ?>">ver detalhes</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
