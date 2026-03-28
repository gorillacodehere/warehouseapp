<?php
// index.php – Front controller / Router

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Korisnik.php';
require_once __DIR__ . '/classes/Artikal.php';
require_once __DIR__ . '/classes/Kategorija.php';

$page = $_GET['page'] ?? 'dashboard';

// ── Javne stranice ──────────────────────────────────
if ($page === 'prijava') { require __DIR__ . '/pages/prijava.php'; exit; }
if ($page === 'odjava') {
    session_destroy();
    header('Location: /skladiste/index.php?page=prijava');
    exit;
}

// ── Privatne stranice (zahtjevaju login) ────────────
Korisnik::provjeriSesiju();

$allowedPages = ['dashboard','artikli','transakcije','kategorije','dobavljaci','admin'];
if (!in_array($page, $allowedPages)) $page = 'dashboard';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/pages/' . $page . '.php';
require __DIR__ . '/includes/footer.php';
