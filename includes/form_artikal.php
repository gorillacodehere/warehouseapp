<?php // includes/form_artikal.php ?>
<div class="row g-3">
    <div class="col-md-8">
        <label class="sk-label">Naziv *</label>
        <input type="text" name="naziv" class="sk-form-control" required placeholder="Naziv artikla">
    </div>
    <div class="col-md-4">
        <label class="sk-label">SKU</label>
        <input type="text" name="sku" class="sk-form-control" placeholder="EL-001">
    </div>
    <div class="col-12">
        <label class="sk-label">Opis</label>
        <textarea name="opis" class="sk-form-control" rows="2" placeholder="Kratki opis..."></textarea>
    </div>
    <div class="col-md-6">
        <label class="sk-label">Kategorija</label>
        <select name="kategorija_id" class="sk-form-control">
            <option value="">— Izaberite —</option>
            <?php foreach ($kategorije as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="sk-label">Dobavljač</label>
        <select name="dobavljac_id" class="sk-form-control">
            <option value="">— Izaberite —</option>
            <?php foreach ($dobavljaci as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="sk-label">Početna kolicina</label>
        <input type="number" name="kolicina" class="sk-form-control" min="0" value="0">
    </div>
    <div class="col-md-4">
        <label class="sk-label">Min. kolicina</label>
        <input type="number" name="min_kolicina" class="sk-form-control" min="0" value="5">
    </div>
    <div class="col-md-4">
        <label class="sk-label">Jed. mere</label>
        <select name="jedinica_mere" class="sk-form-control">
            <?php foreach (['kom','kg','g','l','ml','m','set','kut','riz'] as $j): ?>
            <option value="<?= $j ?>"><?= $j ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="sk-label">Cena nabavke (RSD)</label>
        <input type="number" name="cena_nabavke" class="sk-form-control" step="0.01" min="0" value="0">
    </div>
    <div class="col-md-4">
        <label class="sk-label">Cena prodaje (RSD)</label>
        <input type="number" name="cena_prodaje" class="sk-form-control" step="0.01" min="0" value="0">
    </div>
    <div class="col-md-4">
        <label class="sk-label">Lokacija</label>
        <input type="text" name="lokacija" class="sk-form-control" placeholder="Polica A1">
    </div>
</div>
