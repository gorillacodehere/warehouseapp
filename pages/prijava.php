<?php
// pages/prijava.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Korisnik.php';

if (!empty($_SESSION['korisnik_id'])) {
    header('Location: /skladiste/index.php?page=dashboard'); exit;
}

$greska = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $k = new Korisnik();
    if ($k->prijava(trim($_POST['email'] ?? ''), $_POST['lozinka'] ?? '')) {
        header('Location: /skladiste/index.php?page=dashboard'); exit;
    }
    $greska = 'Neispravni podaci za prijavu.';
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava – <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/skladiste/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="sk-login-wrap">
    <div class="sk-login-box">
        <div class="sk-login-logo">
            <div class="brand-icon"><i class="bi bi-boxes"></i></div>
            <h1><?= APP_NAME ?></h1>
            <p>Sistem za upravljanje skladištem</p>
        </div>

        <?php if ($greska): ?>
        <div class="sk-alert sk-alert-danger mb-3">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($greska) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="sk-label">Email adresa</label>
                <input type="email" name="email" class="sk-form-control"
                       placeholder="vas@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="sk-label">Lozinka</label>
                <input type="password" name="lozinka" class="sk-form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-sk-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Prijava
            </button>
        </form>

        <div class="mt-4 p-3 rounded" style="background:rgba(245,166,35,.07);border:1px solid rgba(245,166,35,.2);">
            <div class="sk-label mb-1">Demo pristup:</div>
            <div class="d-flex gap-3 flex-wrap" style="font-size:.82rem;font-family:'Space Mono',monospace;">
                <div><span style="color:#64748b">admin:</span> admin@skladiste.com / password</div>
                <div><span style="color:#64748b">user:</span> petar@skladiste.com / password</div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
