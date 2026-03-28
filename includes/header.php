<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
$page = $_GET['page'] ?? 'dashboard';
$isLoggedIn = !empty($_SESSION['korisnik_id']);
$isAdmin = ($isLoggedIn && $_SESSION['korisnik_uloga'] === 'admin');
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> – <?= htmlspecialchars(ucfirst($page)) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/skladiste/assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php if ($isLoggedIn): ?>
<nav class="navbar navbar-expand-lg navbar-dark sk-navbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/skladiste/index.php">
            <div class="brand-icon"><i class="bi bi-boxes"></i></div>
            <span class="brand-name"><?= APP_NAME ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $page==='dashboard'?'active':'' ?>" href="/skladiste/index.php?page=dashboard">
                        <i class="bi bi-grid-1x2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $page==='artikli'?'active':'' ?>" href="/skladiste/index.php?page=artikli">
                        <i class="bi bi-box-seam"></i> Artikli
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $page==='transakcije'?'active':'' ?>" href="/skladiste/index.php?page=transakcije">
                        <i class="bi bi-arrow-left-right"></i> Transakcije
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $page==='kategorije'?'active':'' ?>" href="/skladiste/index.php?page=kategorije">
                        <i class="bi bi-tags"></i> Kategorije
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $page==='dobavljaci'?'active':'' ?>" href="/skladiste/index.php?page=dobavljaci">
                        <i class="bi bi-truck"></i> Dobavljači
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $page==='admin'?'active':'' ?>" href="/skladiste/index.php?page=admin">
                        <i class="bi bi-shield-lock"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <div class="user-badge">
                    <i class="bi bi-person-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['korisnik_ime']) ?></span>
                    <?php if ($isAdmin): ?>
                    <span class="badge-admin">ADMIN</span>
                    <?php endif; ?>
                </div>
                <a href="/skladiste/index.php?page=odjava" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Odjava
                </a>
            </div>
        </div>
    </div>
</nav>
<div class="sk-content">
<?php endif; ?>
