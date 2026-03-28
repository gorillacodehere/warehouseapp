<?php
// pages/kategorije.php
$model = new Kategorija();
$poruka = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcija = $_POST['akcija'] ?? '';
    if ($akcija === 'kreiraj') {
        $r = $model->kreiraj($_POST);
    } elseif ($akcija === 'azuriraj') {
        $r = $model->azuriraj((int)$_POST['id'], $_POST);
    } elseif ($akcija === 'obrisi') {
        $r = $model->obrisi((int)$_POST['id']);
    }
    $qs = http_build_query(['page'=>'kategorije','poruka_tip'=>$r['uspjeh']?'success':'danger','poruka'=>$r['poruka']]);
    header("Location: /skladiste/index.php?$qs"); exit;
}
if (!empty($_GET['poruka'])) {
    $poruka = ['tip' => $_GET['poruka_tip'] ?? 'success', 'tekst' => $_GET['poruka']];
}

$kategorije = $model->getAll();
?>
<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-tags"></i> Kategorije</div>
    <button class="btn-sk-primary" data-bs-toggle="modal" data-bs-target="#kreirajKatModal">
        <i class="bi bi-plus-lg"></i> Nova kategorija
    </button>
</div>

<?php if ($poruka): ?>
<div class="sk-alert sk-alert-<?= $poruka['tip'] ?>">
    <i class="bi bi-<?= $poruka['tip']==='success'?'check-circle':'exclamation-triangle' ?>"></i>
    <?= htmlspecialchars($poruka['tekst']) ?>
</div>
<?php endif; ?>

<div class="sk-card">
    <table class="sk-table">
        <thead><tr><th>#</th><th>Naziv</th><th>Opis</th><th>Artikala</th><th>Kreirano</th><th style="text-align:right">Akcije</th></tr></thead>
        <tbody>
        <?php foreach ($kategorije as $k): ?>
        <tr>
            <td style="font-family:'Space Mono',monospace;color:var(--sk-muted);font-size:.78rem"><?= $k['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($k['naziv']) ?></td>
            <td style="color:var(--sk-muted);font-size:.85rem"><?= htmlspecialchars($k['opis'] ?? '–') ?></td>
            <td><span class="sk-badge sk-badge-blue"><?= $k['broj_artikala'] ?></span></td>
            <td style="font-size:.8rem;color:var(--sk-muted)"><?= date('d.m.Y', strtotime($k['created_at'])) ?></td>
            <td style="text-align:right">
                <div class="d-flex gap-1 justify-content-end">
                    <button class="btn-sk-outline"
                            data-edit="editKat"
                            data-id="<?= $k['id'] ?>"
                            data-naziv="<?= htmlspecialchars($k['naziv'],ENT_QUOTES) ?>"
                            data-opis="<?= htmlspecialchars($k['opis'],ENT_QUOTES) ?>"
                            style="font-size:.78rem;padding:.3rem .6rem">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="akcija" value="obrisi">
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">
                        <button type="submit" class="btn-sk-danger"
                                data-confirm="Obrisati kategoriju '<?= htmlspecialchars($k['naziv'],ENT_QUOTES) ?>'?"
                                style="font-size:.78rem;padding:.3rem .6rem">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal kreiraj -->
<div class="modal fade" id="kreirajKatModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--sk-accent)"></i>Nova kategorija</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST"><input type="hidden" name="akcija" value="kreiraj">
        <div class="modal-body">
            <div class="mb-3"><label class="sk-label">Naziv *</label><input type="text" name="naziv" class="sk-form-control" required></div>
            <div class="mb-2"><label class="sk-label">Opis</label><textarea name="opis" class="sk-form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
            <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Sačuvaj</button>
        </div></form>
    </div></div>
</div>

<!-- Modal uredi -->
<div class="modal fade" id="editKatModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:var(--sk-accent)"></i>Uredi kategoriju</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST"><input type="hidden" name="akcija" value="azuriraj"><input type="hidden" name="id">
        <div class="modal-body">
            <div class="mb-3"><label class="sk-label">Naziv *</label><input type="text" name="naziv" class="sk-form-control" required></div>
            <div class="mb-2"><label class="sk-label">Opis</label><textarea name="opis" class="sk-form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
            <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Ažuriraj</button>
        </div></form>
    </div></div>
</div>
