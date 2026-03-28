<?php
// pages/dobavljaci.php
$model = new Dobavljac();
$poruka = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcija = $_POST['akcija'] ?? '';
    if ($akcija === 'kreiraj')   $r = $model->kreiraj($_POST);
    elseif ($akcija === 'azuriraj') $r = $model->azuriraj((int)$_POST['id'], $_POST);
    elseif ($akcija === 'obrisi')   $r = $model->obrisi((int)$_POST['id']);
    $qs = http_build_query(['page'=>'dobavljaci','poruka_tip'=>$r['uspjeh']?'success':'danger','poruka'=>$r['poruka']]);
    header("Location: /skladiste/index.php?$qs"); exit;
}
if (!empty($_GET['poruka'])) {
    $poruka = ['tip' => $_GET['poruka_tip'] ?? 'success', 'tekst' => $_GET['poruka']];
}
$dobavljaci = $model->getAll();

function formDob(array $d = []): void { ?>
    <div class="mb-3"><label class="sk-label">Naziv *</label>
    <input type="text" name="naziv" class="sk-form-control" required value="<?= htmlspecialchars($d['naziv']??'') ?>"></div>
    <div class="row g-2">
        <div class="col-md-6 mb-3"><label class="sk-label">Kontakt osoba</label>
        <input type="text" name="kontakt_osoba" class="sk-form-control" value="<?= htmlspecialchars($d['kontakt_osoba']??'') ?>"></div>
        <div class="col-md-6 mb-3"><label class="sk-label">Email</label>
        <input type="email" name="email" class="sk-form-control" value="<?= htmlspecialchars($d['email']??'') ?>"></div>
        <div class="col-md-6 mb-3"><label class="sk-label">Telefon</label>
        <input type="text" name="telefon" class="sk-form-control" value="<?= htmlspecialchars($d['telefon']??'') ?>"></div>
        <div class="col-md-6 mb-3"><label class="sk-label">Adresa</label>
        <input type="text" name="adresa" class="sk-form-control" value="<?= htmlspecialchars($d['adresa']??'') ?>"></div>
    </div>
<?php }
?>
<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-truck"></i> Dobavljači</div>
    <button class="btn-sk-primary" data-bs-toggle="modal" data-bs-target="#kreirajDobModal">
        <i class="bi bi-plus-lg"></i> Novi dobavljač
    </button>
</div>

<?php if ($poruka): ?>
<div class="sk-alert sk-alert-<?= $poruka['tip'] ?>">
    <i class="bi bi-<?= $poruka['tip']==='success'?'check-circle':'exclamation-triangle' ?>"></i>
    <?= htmlspecialchars($poruka['tekst']) ?>
</div>
<?php endif; ?>

<div class="row g-3">
<?php foreach ($dobavljaci as $d): ?>
<div class="col-md-6 col-xl-4">
    <div class="sk-card h-100">
        <div class="sk-card-header">
            <div class="sk-card-title"><i class="bi bi-building"></i> <?= htmlspecialchars($d['naziv']) ?></div>
            <span class="sk-badge sk-badge-blue"><?= $d['broj_artikala'] ?> artikala</span>
        </div>
        <div class="sk-card-body">
            <div class="d-flex flex-column gap-2" style="font-size:.85rem">
                <?php if ($d['kontakt_osoba']): ?>
                <div><i class="bi bi-person me-1" style="color:var(--sk-muted)"></i><?= htmlspecialchars($d['kontakt_osoba']) ?></div>
                <?php endif; ?>
                <?php if ($d['email']): ?>
                <div><i class="bi bi-envelope me-1" style="color:var(--sk-muted)"></i>
                    <a href="mailto:<?= htmlspecialchars($d['email']) ?>" style="color:var(--sk-accent)"><?= htmlspecialchars($d['email']) ?></a></div>
                <?php endif; ?>
                <?php if ($d['telefon']): ?>
                <div><i class="bi bi-telephone me-1" style="color:var(--sk-muted)"></i><?= htmlspecialchars($d['telefon']) ?></div>
                <?php endif; ?>
                <?php if ($d['adresa']): ?>
                <div><i class="bi bi-geo-alt me-1" style="color:var(--sk-muted)"></i><?= htmlspecialchars($d['adresa']) ?></div>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn-sk-outline"
                        data-edit="editDob"
                        data-id="<?= $d['id'] ?>"
                        data-naziv="<?= htmlspecialchars($d['naziv'],ENT_QUOTES) ?>"
                        data-kontakt_osoba="<?= htmlspecialchars($d['kontakt_osoba'],ENT_QUOTES) ?>"
                        data-email="<?= htmlspecialchars($d['email'],ENT_QUOTES) ?>"
                        data-telefon="<?= htmlspecialchars($d['telefon'],ENT_QUOTES) ?>"
                        data-adresa="<?= htmlspecialchars($d['adresa'],ENT_QUOTES) ?>"
                        style="font-size:.8rem;flex:1">
                    <i class="bi bi-pencil me-1"></i> Uredi
                </button>
                <form method="POST">
                    <input type="hidden" name="akcija" value="obrisi">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <button type="submit" class="btn-sk-danger"
                            data-confirm="Obrisati dobavljača '<?= htmlspecialchars($d['naziv'],ENT_QUOTES) ?>'?"
                            style="font-size:.8rem;padding:.35rem .7rem">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Modal kreiraj -->
<div class="modal fade" id="kreirajDobModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--sk-accent)"></i>Novi dobavljač</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST"><input type="hidden" name="akcija" value="kreiraj">
        <div class="modal-body"><?php formDob(); ?></div>
        <div class="modal-footer">
            <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
            <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Sačuvaj</button>
        </div></form>
    </div></div>
</div>

<!-- Modal uredi -->
<div class="modal fade" id="editDobModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:var(--sk-accent)"></i>Uredi dobavljača</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST"><input type="hidden" name="akcija" value="azuriraj"><input type="hidden" name="id">
        <div class="modal-body"><?php formDob(); ?></div>
        <div class="modal-footer">
            <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
            <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Ažuriraj</button>
        </div></form>
    </div></div>
</div>
