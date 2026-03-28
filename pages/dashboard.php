<?php
// pages/dashboard.php
$artikal = new Artikal();
$stat    = $artikal->getStatistika();
$nizakStock = $artikal->getAll(['nizak_stock' => true]);
$posTransakcije = $artikal->getTransakcije(0, 8);

function formatBroj(float $n): string {
    return number_format($n, 0, ',', '.');
}
?>

<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-grid-1x2"></i> Dashboard</div>
    <span style="font-size:.82rem;color:var(--sk-muted);font-family:'Space Mono',monospace">
        <?= date('d.m.Y H:i') ?>
    </span>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-value"><?= $stat['ukupno_artikala'] ?></div>
                <div class="stat-label">Ukupno artikala</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value"><?= $stat['nizak_stock'] ?></div>
                <div class="stat-label">Nizak stock</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-currency-exchange"></i></div>
            <div>
                <div class="stat-value"><?= formatBroj($stat['vrednost_stanja']) ?></div>
                <div class="stat-label">Vrednost stanja (RSD)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-tags"></i></div>
            <div>
                <div class="stat-value"><?= $stat['kategorije'] ?></div>
                <div class="stat-label">Kategorije</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Nizak stock upozorenja -->
    <div class="col-lg-6">
        <div class="sk-card h-100">
            <div class="sk-card-header">
                <div class="sk-card-title"><i class="bi bi-exclamation-triangle"></i> Nizak stock</div>
                <a href="/skladiste/index.php?page=artikli&nizak_stock=1" class="btn-sk-outline" style="font-size:.78rem;padding:.3rem .7rem;">
                    Vidi sve
                </a>
            </div>
            <?php if (empty($nizakStock)): ?>
            <div class="sk-card-body">
                <div class="sk-alert sk-alert-success"><i class="bi bi-check-circle"></i> Svi artikli imaju dovoljan stock!</div>
            </div>
            <?php else: ?>
            <table class="sk-table">
                <thead>
                    <tr>
                        <th>Artikal</th>
                        <th>Stanje</th>
                        <th>Minimum</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($nizakStock, 0, 6) as $a): ?>
                <tr>
                    <td>
                        <div style="font-weight:500"><?= htmlspecialchars($a['naziv']) ?></div>
                        <div style="font-size:.75rem;color:var(--sk-muted);font-family:'Space Mono',monospace"><?= htmlspecialchars($a['sku']) ?></div>
                    </td>
                    <td>
                        <span class="sk-badge sk-badge-red"><?= $a['kolicina'] ?> <?= htmlspecialchars($a['jedinica_mere']) ?></span>
                    </td>
                    <td style="color:var(--sk-muted)"><?= $a['min_kolicina'] ?></td>
                    <td>
                        <a href="/skladiste/index.php?page=artikli&akcija=ulaz&id=<?= $a['id'] ?>" class="btn-sk-primary" style="font-size:.75rem;padding:.3rem .7rem;">
                            <i class="bi bi-plus"></i> Ulaz
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Posljednje transakcije -->
    <div class="col-lg-6">
        <div class="sk-card h-100">
            <div class="sk-card-header">
                <div class="sk-card-title"><i class="bi bi-arrow-left-right"></i> Posljednje transakcije</div>
                <a href="/skladiste/index.php?page=transakcije" class="btn-sk-outline" style="font-size:.78rem;padding:.3rem .7rem;">Vidi sve</a>
            </div>
            <table class="sk-table">
                <thead>
                    <tr><th>Artikal</th><th>Tip</th><th>Kol.</th><th>Ko</th><th>Datum</th></tr>
                </thead>
                <tbody>
                <?php foreach ($posTransakcije as $t): ?>
                <?php
                    $tipBadge = match($t['tip']) {
                        'ulaz'     => 'sk-badge-green',
                        'izlaz'    => 'sk-badge-red',
                        default    => 'sk-badge-blue',
                    };
                    $tipIcon = match($t['tip']) {
                        'ulaz'  => 'bi-arrow-down-circle',
                        'izlaz' => 'bi-arrow-up-circle',
                        default => 'bi-pencil',
                    };
                ?>
                <tr>
                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($t['artikal']) ?>">
                        <?= htmlspecialchars($t['artikal']) ?>
                    </td>
                    <td><span class="sk-badge <?= $tipBadge ?>"><i class="bi <?= $tipIcon ?>"></i> <?= $t['tip'] ?></span></td>
                    <td style="font-family:'Space Mono',monospace;font-size:.85rem"><?= $t['kolicina'] ?></td>
                    <td style="color:var(--sk-muted);font-size:.8rem"><?= htmlspecialchars(explode(' ', $t['korisnik'])[0]) ?></td>
                    <td style="color:var(--sk-muted);font-size:.78rem;white-space:nowrap">
                        <?= date('d.m H:i', strtotime($t['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
