<?php
// pages/transakcije.php
$artikalModel = new Artikal();
$transakcije  = $artikalModel->getTransakcije(0, 100);
?>
<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-arrow-left-right"></i> Transakcije</div>
    <span style="font-size:.82rem;color:var(--sk-muted)"><?= count($transakcije) ?> zapisa</span>
</div>

<div class="sk-card">
    <div class="table-responsive">
    <table class="sk-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Datum</th>
                <th>Artikal</th>
                <th>Tip</th>
                <th>Količina</th>
                <th>Korisnik</th>
                <th>Napomena</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($transakcije as $t):
            $tipBadge = match($t['tip']) {
                'ulaz'  => 'sk-badge-green',
                'izlaz' => 'sk-badge-red',
                default => 'sk-badge-blue',
            };
            $tipIcon = match($t['tip']) {
                'ulaz'  => 'bi-arrow-down-circle',
                'izlaz' => 'bi-arrow-up-circle',
                default => 'bi-pencil-square',
            };
        ?>
        <tr>
            <td style="font-family:'Space Mono',monospace;color:var(--sk-muted);font-size:.78rem"><?= $t['id'] ?></td>
            <td style="font-size:.82rem;white-space:nowrap;color:var(--sk-muted)">
                <?= date('d.m.Y', strtotime($t['created_at'])) ?><br>
                <span style="font-family:'Space Mono',monospace;font-size:.72rem"><?= date('H:i', strtotime($t['created_at'])) ?></span>
            </td>
            <td>
                <a href="/skladiste/index.php?page=artikli&search=<?= urlencode($t['artikal']) ?>"
                   style="color:var(--sk-text);text-decoration:none;font-weight:500">
                    <?= htmlspecialchars($t['artikal']) ?>
                </a>
            </td>
            <td><span class="sk-badge <?= $tipBadge ?>"><i class="bi <?= $tipIcon ?>"></i> <?= $t['tip'] ?></span></td>
            <td style="font-family:'Space Mono',monospace;font-size:.9rem;font-weight:700">
                <?= $t['tip']==='izlaz' ? '−' : '+' ?><?= $t['kolicina'] ?>
            </td>
            <td style="font-size:.85rem;color:var(--sk-muted)"><?= htmlspecialchars($t['korisnik']) ?></td>
            <td style="font-size:.82rem;color:var(--sk-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <?= htmlspecialchars($t['napomena'] ?? '–') ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
