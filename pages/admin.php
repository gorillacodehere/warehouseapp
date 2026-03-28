<?php
// pages/admin.php
Korisnik::provjeriAdmin();

$model  = new Korisnik();
$poruka = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcija = $_POST['akcija'] ?? '';
    if ($akcija === 'registracija') {
        if (!empty($_POST['lozinka']) && $_POST['lozinka'] !== $_POST['lozinka2']) {
            $r = ['uspjeh' => false, 'poruka' => 'Lozinke se ne podudaraju.'];
        } else {
            $r = $model->registracija($_POST);
        }
    } elseif ($akcija === 'azuriraj') {
        $r = $model->azuriraj((int)$_POST['id'], $_POST);
    } elseif ($akcija === 'obrisi') {
        $r = $model->obrisi((int)$_POST['id']);
    } else {
        $r = ['uspjeh' => false, 'poruka' => 'Nepoznata akcija.'];
    }
    $qs = http_build_query(['page'=>'admin','poruka_tip'=>$r['uspjeh']?'success':'danger','poruka'=>$r['poruka']]);
    header("Location: /skladiste/index.php?$qs"); exit;
}

if (!empty($_GET['poruka'])) {
    $poruka = ['tip' => $_GET['poruka_tip'] ?? 'success', 'tekst' => $_GET['poruka']];
}

$korisnici = $model->getAll();

$db = Database::getInstance();
$totalTrans = $db->query("SELECT COUNT(*) AS n FROM transakcije")->fetch_assoc()['n'];
$totalArt   = $db->query("SELECT COUNT(*) AS n FROM artikli WHERE aktivan=1")->fetch_assoc()['n'];
$totalDob   = $db->query("SELECT COUNT(*) AS n FROM dobavljaci WHERE aktivan=1")->fetch_assoc()['n'];
?>

<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-shield-lock"></i> Admin Panel</div>
    <button class="btn-sk-primary" data-bs-toggle="modal" data-bs-target="#kreirajKorModal">
        <i class="bi bi-person-plus"></i> Novi korisnik
    </button>
</div>

<?php if ($poruka): ?>
<div class="sk-alert sk-alert-<?= $poruka['tip'] ?>">
    <i class="bi bi-<?= $poruka['tip']==='success'?'check-circle':'exclamation-triangle' ?>"></i>
    <?= htmlspecialchars($poruka['tekst']) ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div><div class="stat-value"><?= count($korisnici) ?></div><div class="stat-label">Korisnici</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-value"><?= $totalArt ?></div><div class="stat-label">Artikli</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-left-right"></i></div>
            <div><div class="stat-value"><?= $totalTrans ?></div><div class="stat-label">Transakcije</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-truck"></i></div>
            <div><div class="stat-value"><?= $totalDob ?></div><div class="stat-label">Dobavljači</div></div>
        </div>
    </div>
</div>

<div class="sk-card">
    <div class="sk-card-header">
        <div class="sk-card-title"><i class="bi bi-people"></i> Upravljanje korisnicima</div>
    </div>
    <div class="table-responsive">
    <table class="sk-table">
        <thead>
            <tr>
                <th>#</th><th>Ime i prezime</th><th>Email</th><th>Uloga</th>
                <th>Status</th><th>Registrovan</th><th style="text-align:right">Akcije</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($korisnici as $k): ?>
        <tr>
            <td style="font-family:'Space Mono',monospace;color:var(--sk-muted);font-size:.78rem"><?= $k['id'] ?></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(245,166,35,.15);
                                border:1px solid rgba(245,166,35,.3);display:flex;align-items:center;
                                justify-content:center;font-size:.85rem;font-weight:700;color:var(--sk-accent);flex-shrink:0">
                        <?= strtoupper(substr($k['ime'],0,1)) ?>
                    </div>
                    <span style="font-weight:500"><?= htmlspecialchars($k['ime'].' '.$k['prezime']) ?></span>
                </div>
            </td>
            <td style="color:var(--sk-muted);font-size:.88rem"><?= htmlspecialchars($k['email']) ?></td>
            <td>
                <?php if ($k['uloga'] === 'admin'): ?>
                    <span class="sk-badge sk-badge-orange"><i class="bi bi-shield-fill"></i> admin</span>
                <?php else: ?>
                    <span class="sk-badge sk-badge-gray"><i class="bi bi-person"></i> korisnik</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($k['aktivan']): ?>
                    <span class="sk-badge sk-badge-green"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Aktivan</span>
                <?php else: ?>
                    <span class="sk-badge sk-badge-red"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Neaktivan</span>
                <?php endif; ?>
            </td>
            <td style="font-size:.82rem;color:var(--sk-muted)"><?= date('d.m.Y', strtotime($k['created_at'])) ?></td>
            <td style="text-align:right">
                <div class="d-flex gap-1 justify-content-end">
                    <button class="btn-sk-outline"
                            data-edit="editKor"
                            data-id="<?= $k['id'] ?>"
                            data-ime="<?= htmlspecialchars($k['ime'],ENT_QUOTES) ?>"
                            data-prezime="<?= htmlspecialchars($k['prezime'],ENT_QUOTES) ?>"
                            data-email="<?= htmlspecialchars($k['email'],ENT_QUOTES) ?>"
                            data-uloga="<?= $k['uloga'] ?>"
                            data-aktivan="<?= $k['aktivan'] ?>"
                            style="font-size:.78rem;padding:.3rem .6rem">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <?php if ($k['id'] != $_SESSION['korisnik_id']): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="akcija" value="obrisi">
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">
                        <button type="submit" class="btn-sk-danger"
                                data-confirm="Obrisati korisnika '<?= htmlspecialchars($k['ime'].' '.$k['prezime'],ENT_QUOTES) ?>'?"
                                style="font-size:.78rem;padding:.3rem .6rem">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal: Novi korisnik -->
<div class="modal fade" id="kreirajKorModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-person-plus me-2" style="color:var(--sk-accent)"></i>Novi korisnik</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
            <input type="hidden" name="akcija" value="registracija">
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="sk-label">Ime *</label>
                    <input type="text" name="ime" class="sk-form-control" required></div>
                    <div class="col-6"><label class="sk-label">Prezime *</label>
                    <input type="text" name="prezime" class="sk-form-control" required></div>
                    <div class="col-12"><label class="sk-label">Email *</label>
                    <input type="email" name="email" class="sk-form-control" required></div>
                    <div class="col-6"><label class="sk-label">Lozinka *</label>
                    <input type="password" name="lozinka" class="sk-form-control" required></div>
                    <div class="col-6"><label class="sk-label">Potvrdi lozinku *</label>
                    <input type="password" name="lozinka2" class="sk-form-control" required></div>
                    <div class="col-12"><label class="sk-label">Uloga</label>
                    <select name="uloga" class="sk-form-control">
                        <option value="korisnik">Korisnik</option>
                        <option value="admin">Admin</option>
                    </select></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
                <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Kreiraj</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Modal: Uredi korisnika -->
<div class="modal fade" id="editKorModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:var(--sk-accent)"></i>Uredi korisnika</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
            <input type="hidden" name="akcija" value="azuriraj">
            <input type="hidden" name="id">
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="sk-label">Ime *</label>
                    <input type="text" name="ime" class="sk-form-control" required></div>
                    <div class="col-6"><label class="sk-label">Prezime *</label>
                    <input type="text" name="prezime" class="sk-form-control" required></div>
                    <div class="col-12"><label class="sk-label">Email *</label>
                    <input type="email" name="email" class="sk-form-control" required></div>
                    <div class="col-6"><label class="sk-label">Uloga</label>
                    <select name="uloga" class="sk-form-control">
                        <option value="korisnik">Korisnik</option>
                        <option value="admin">Admin</option>
                    </select></div>
                    <div class="col-6"><label class="sk-label">Status</label>
                    <select name="aktivan" class="sk-form-control">
                        <option value="1">Aktivan</option>
                        <option value="0">Neaktivan</option>
                    </select></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
                <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Ažuriraj</button>
            </div>
        </form>
    </div></div>
</div>
