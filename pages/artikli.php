<?php
// pages/artikli.php
$artikalModel  = new Artikal();
$kategorijaModel = new Kategorija();
$dobavljacModel  = new Dobavljac();

$poruka = '';

// ── POST akcije ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcija = $_POST['akcija'] ?? '';

    if ($akcija === 'kreiraj') {
        $r = $artikalModel->kreiraj($_POST);
        $poruka = ['tip' => $r['uspjeh'] ? 'success' : 'danger', 'tekst' => $r['poruka']];
    } elseif ($akcija === 'azuriraj') {
        $r = $artikalModel->azuriraj((int)$_POST['id'], $_POST);
        $poruka = ['tip' => $r['uspjeh'] ? 'success' : 'danger', 'tekst' => $r['poruka']];
    } elseif ($akcija === 'obrisi') {
        $r = $artikalModel->obrisi((int)$_POST['id']);
        $poruka = ['tip' => $r['uspjeh'] ? 'success' : 'danger', 'tekst' => $r['poruka']];
    } elseif (in_array($akcija, ['ulaz','izlaz','korekcija'])) {
        $r = $artikalModel->promeniKolicinu((int)$_POST['id'], (int)$_POST['kolicina'], $akcija, $_POST['napomena'] ?? '');
        $poruka = ['tip' => $r['uspjeh'] ? 'success' : 'danger', 'tekst' => $r['poruka']];
    }
    // Redirect to avoid re-POST
    $qs = http_build_query(array_filter(['page'=>'artikli','poruka_tip'=>$poruka['tip'],'poruka'=>$poruka['tekst']]));
    header("Location: /skladiste/index.php?$qs"); exit;
}

// Poruka iz redirect-a
if (!empty($_GET['poruka'])) {
    $poruka = ['tip' => $_GET['poruka_tip'] ?? 'success', 'tekst' => $_GET['poruka']];
}

// ── Filter / pretraga ────────────────────────────────
$filter = [
    'search'       => $_GET['search'] ?? '',
    'kategorija_id'=> $_GET['kategorija_id'] ?? '',
    'dobavljac_id' => $_GET['dobavljac_id'] ?? '',
    'nizak_stock'  => isset($_GET['nizak_stock']),
];

$artikli    = $artikalModel->getAll($filter);
$kategorije = $kategorijaModel->getAll();
$dobavljaci = $dobavljacModel->getAll();
?>

<div class="sk-page-header">
    <div class="sk-page-title"><i class="bi bi-box-seam"></i> Artikli</div>
    <button class="btn-sk-primary" data-bs-toggle="modal" data-bs-target="#kreirajModal">
        <i class="bi bi-plus-lg"></i> Novi artikal
    </button>
</div>

<?php if ($poruka): ?>
<div class="sk-alert sk-alert-<?= $poruka['tip'] ?>">
    <i class="bi bi-<?= $poruka['tip']==='success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <?= htmlspecialchars($poruka['tekst']) ?>
</div>
<?php endif; ?>

<!-- Search & Filter bar -->
<form method="GET" class="sk-search-bar">
    <input type="hidden" name="page" value="artikli">
    <input type="text" id="liveSearch" name="search" class="sk-form-control" style="max-width:250px"
           placeholder="🔍  Pretraga..." value="<?= htmlspecialchars($filter['search']) ?>">
    <select name="kategorija_id" class="sk-form-control" style="max-width:180px" onchange="this.form.submit()">
        <option value="">Sve kategorije</option>
        <?php foreach ($kategorije as $k): ?>
        <option value="<?= $k['id'] ?>" <?= $filter['kategorija_id']==$k['id']?'selected':'' ?>>
            <?= htmlspecialchars($k['naziv']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <select name="dobavljac_id" class="sk-form-control" style="max-width:180px" onchange="this.form.submit()">
        <option value="">Svi dobavljači</option>
        <?php foreach ($dobavljaci as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $filter['dobavljac_id']==$d['id']?'selected':'' ?>>
            <?= htmlspecialchars($d['naziv']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <label style="display:flex;align-items:center;gap:.4rem;color:var(--sk-muted);font-size:.85rem;cursor:pointer;white-space:nowrap">
        <input type="checkbox" name="nizak_stock" value="1" <?= $filter['nizak_stock']?'checked':'' ?> onchange="this.form.submit()">
        Samo nizak stock
    </label>
    <?php if (array_filter($filter)): ?>
    <a href="/skladiste/index.php?page=artikli" class="btn-sk-outline" style="white-space:nowrap">✕ Reset</a>
    <?php endif; ?>
    <span class="ms-auto" style="color:var(--sk-muted);font-size:.8rem;font-family:'Space Mono',monospace">
        <?= count($artikli) ?> artikala
    </span>
</form>

<!-- Table -->
<div class="sk-card">
    <div class="table-responsive">
    <table class="sk-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Naziv</th>
                <th>Kategorija</th>
                <th>Dobavljač</th>
                <th>Stanje</th>
                <th>Lokacija</th>
                <th>Cena prod.</th>
                <th style="text-align:right">Akcije</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($artikli)): ?>
        <tr><td colspan="8" class="text-center py-4" style="color:var(--sk-muted)">Nema artikala.</td></tr>
        <?php endif; ?>
        <?php foreach ($artikli as $a):
            $pct = $a['min_kolicina'] > 0 ? ($a['kolicina'] / ($a['min_kolicina'] * 2)) * 100 : 100;
            $stockClass = $a['kolicina'] <= $a['min_kolicina'] ? 'sk-badge-red' : ($a['kolicina'] <= $a['min_kolicina'] * 1.5 ? 'sk-badge-orange' : 'sk-badge-green');
        ?>
        <tr>
            <td><span style="font-family:'Space Mono',monospace;font-size:.8rem;color:var(--sk-muted)"><?= htmlspecialchars($a['sku']) ?></span></td>
            <td>
                <div style="font-weight:500"><?= htmlspecialchars($a['naziv']) ?></div>
                <?php if ($a['opis']): ?>
                <div style="font-size:.75rem;color:var(--sk-muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= htmlspecialchars($a['opis']) ?>
                </div>
                <?php endif; ?>
            </td>
            <td><?= $a['kategorija'] ? '<span class="sk-badge sk-badge-blue">'.htmlspecialchars($a['kategorija']).'</span>' : '–' ?></td>
            <td style="font-size:.85rem;color:var(--sk-muted)"><?= htmlspecialchars($a['dobavljac'] ?? '–') ?></td>
            <td>
                <div class="stock-bar-wrap">
                    <span class="sk-badge <?= $stockClass ?>"><?= $a['kolicina'] ?> <?= htmlspecialchars($a['jedinica_mere']) ?></span>
                    <div class="stock-bar-bg"><div class="stock-bar-fill" data-pct="<?= min($pct,100) ?>"></div></div>
                </div>
            </td>
            <td style="font-size:.82rem;color:var(--sk-muted)"><?= htmlspecialchars($a['lokacija'] ?? '–') ?></td>
            <td style="font-family:'Space Mono',monospace;font-size:.85rem"><?= number_format($a['cena_prodaje'], 2, ',', '.') ?> RSD</td>
            <td style="text-align:right">
                <div class="d-flex gap-1 justify-content-end">
                    <!-- Ulaz -->
                    <button class="btn-sk-primary"
                            data-edit="ulaz"
                            data-id="<?= $a['id'] ?>"
                            data-naziv="<?= htmlspecialchars($a['naziv']) ?>"
                            style="font-size:.75rem;padding:.3rem .6rem;"
                            title="Ulaz">
                        <i class="bi bi-arrow-down-circle"></i>
                    </button>
                    <!-- Izlaz -->
                    <button class="btn-sk-outline"
                            data-edit="izlaz"
                            data-id="<?= $a['id'] ?>"
                            data-naziv="<?= htmlspecialchars($a['naziv']) ?>"
                            style="font-size:.75rem;padding:.3rem .6rem;color:var(--sk-accent);"
                            title="Izlaz">
                        <i class="bi bi-arrow-up-circle"></i>
                    </button>
                    <!-- Edit -->
                    <button class="btn-sk-outline"
                            data-edit="edit"
                            data-id="<?= $a['id'] ?>"
                            data-naziv="<?= htmlspecialchars($a['naziv'],ENT_QUOTES) ?>"
                            data-sku="<?= htmlspecialchars($a['sku'],ENT_QUOTES) ?>"
                            data-opis="<?= htmlspecialchars($a['opis'],ENT_QUOTES) ?>"
                            data-kategorija_id="<?= $a['kategorija_id'] ?>"
                            data-dobavljac_id="<?= $a['dobavljac_id'] ?>"
                            data-min_kolicina="<?= $a['min_kolicina'] ?>"
                            data-cena_nabavke="<?= $a['cena_nabavke'] ?>"
                            data-cena_prodaje="<?= $a['cena_prodaje'] ?>"
                            data-jedinica_mere="<?= htmlspecialchars($a['jedinica_mere'],ENT_QUOTES) ?>"
                            data-lokacija="<?= htmlspecialchars($a['lokacija'],ENT_QUOTES) ?>"
                            style="font-size:.75rem;padding:.3rem .6rem;"
                            title="Uredi">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!-- Delete -->
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="akcija" value="obrisi">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn-sk-danger"
                                data-confirm="Obrisati '<?= htmlspecialchars($a['naziv'],ENT_QUOTES) ?>'?"
                                style="font-size:.75rem;padding:.3rem .6rem;">
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
</div>


<!-- Modal: Novi artikal -->
<div class="modal fade" id="kreirajModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--sk-accent)"></i>Novi artikal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="akcija" value="kreiraj">
                <div class="modal-body">
                    <?php include __DIR__ . '/../includes/form_artikal.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
                    <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Sačuvaj</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Uredi artikal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:var(--sk-accent)"></i>Uredi artikal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="akcija" value="azuriraj">
                <input type="hidden" name="id">
                <div class="modal-body">
                    <?php include __DIR__ . '/../includes/form_artikal.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
                    <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Ažuriraj</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Ulaz -->
<?php foreach ([['ulaz','Ulaz robe','bi-arrow-down-circle','sk-badge-green'],['izlaz','Izlaz robe','bi-arrow-up-circle','sk-badge-red']] as [$tip,$label,$icon,$badge]): ?>
<div class="modal fade" id="<?= $tip ?>Modal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi <?= $icon ?> me-2" style="color:var(--sk-accent)"></i><?= $label ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="akcija" value="<?= $tip ?>">
                <input type="hidden" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sk-label">Artikal</label>
                        <div id="<?= $tip ?>NazivDisplay" style="font-weight:500;padding:.5rem;background:var(--sk-surface2);border-radius:4px;font-size:.9rem"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sk-label">Količina</label>
                        <input type="number" name="kolicina" class="sk-form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="sk-label">Napomena</label>
                        <input type="text" name="napomena" class="sk-form-control" placeholder="Opciono...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sk-outline" data-bs-dismiss="modal">Otkaži</button>
                    <button type="submit" class="btn-sk-primary"><i class="bi bi-check-lg me-1"></i> Potvrdi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
// Posebna logika za ulaz/izlaz modals (naziv display)
['ulaz','izlaz'].forEach(tip => {
    document.querySelectorAll(`[data-edit="${tip}"]`).forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(tip + 'Modal');
            modal.querySelector('[name="id"]').value = btn.dataset.id;
            document.getElementById(tip + 'NazivDisplay').textContent = btn.dataset.naziv;
            new bootstrap.Modal(modal).show();
        });
    });
});
</script>
