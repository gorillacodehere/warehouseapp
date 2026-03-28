<?php
// classes/Korisnik.php

require_once __DIR__ . '/../config/database.php';

class Korisnik {
    private Database $db;
    private int $id;
    private string $ime;
    private string $prezime;
    private string $email;
    private string $uloga;
    private bool $aktivan;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Getteri ──────────────────────────────────────
    public function getId(): int { return $this->id; }
    public function getIme(): string { return $this->ime; }
    public function getPrezime(): string { return $this->prezime; }
    public function getPunoIme(): string { return $this->ime . ' ' . $this->prezime; }
    public function getEmail(): string { return $this->email; }
    public function getUloga(): string { return $this->uloga; }
    public function isAktivan(): bool { return $this->aktivan; }
    public function isAdmin(): bool { return $this->uloga === 'admin'; }

    // ── Autentifikacija ──────────────────────────────
    public function prijava(string $email, string $lozinka): bool {
        $email = $this->db->escape($email);
        $result = $this->db->query("SELECT * FROM korisnici WHERE email='$email' AND aktivan=1 LIMIT 1");
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($lozinka, $row['lozinka'])) {
                $this->ucitajIzNiza($row);
                $_SESSION['korisnik_id']  = $row['id'];
                $_SESSION['korisnik_uloga'] = $row['uloga'];
                $_SESSION['korisnik_ime']   = $row['ime'] . ' ' . $row['prezime'];
                return true;
            }
        }
        return false;
    }

    public function odjava(): void {
        session_destroy();
        session_start();
    }

    public function ucitajPoId(int $id): bool {
        $result = $this->db->query("SELECT * FROM korisnici WHERE id=$id LIMIT 1");
        if ($result && $result->num_rows === 1) {
            $this->ucitajIzNiza($result->fetch_assoc());
            return true;
        }
        return false;
    }

    private function ucitajIzNiza(array $row): void {
        $this->id       = (int)$row['id'];
        $this->ime      = $row['ime'];
        $this->prezime  = $row['prezime'];
        $this->email    = $row['email'];
        $this->uloga    = $row['uloga'];
        $this->aktivan  = (bool)$row['aktivan'];
    }

    // ── CRUD ─────────────────────────────────────────
    public function registracija(array $data): array {
        $ime      = $this->db->escape(trim($data['ime']));
        $prezime  = $this->db->escape(trim($data['prezime']));
        $email    = $this->db->escape(trim($data['email']));
        $lozinka  = password_hash($data['lozinka'], PASSWORD_DEFAULT);
        $uloga    = isset($data['uloga']) ? $this->db->escape($data['uloga']) : 'korisnik';

        // Provjera duplikata
        $check = $this->db->query("SELECT id FROM korisnici WHERE email='$email'");
        if ($check && $check->num_rows > 0) {
            return ['uspjeh' => false, 'poruka' => 'Email je već registrovan.'];
        }

        $sql = "INSERT INTO korisnici (ime, prezime, email, lozinka, uloga)
                VALUES ('$ime','$prezime','$email','$lozinka','$uloga')";
        if ($this->db->query($sql)) {
            return ['uspjeh' => true, 'poruka' => 'Korisnik je uspješno registrovan.'];
        }
        return ['uspjeh' => false, 'poruka' => 'Greška pri registraciji.'];
    }

    public function azuriraj(int $id, array $data): array {
        $ime     = $this->db->escape(trim($data['ime']));
        $prezime = $this->db->escape(trim($data['prezime']));
        $email   = $this->db->escape(trim($data['email']));
        $uloga   = $this->db->escape($data['uloga']);
        $aktivan = (int)$data['aktivan'];

        $sql = "UPDATE korisnici SET ime='$ime', prezime='$prezime', email='$email',
                uloga='$uloga', aktivan=$aktivan WHERE id=$id";
        if ($this->db->query($sql)) {
            return ['uspjeh' => true, 'poruka' => 'Korisnik je ažuriran.'];
        }
        return ['uspjeh' => false, 'poruka' => 'Greška pri ažuriranju.'];
    }

    public function obrisi(int $id): array {
        if ($id === (int)$_SESSION['korisnik_id']) {
            return ['uspjeh' => false, 'poruka' => 'Ne možete obrisati vlastiti nalog.'];
        }
        $this->db->query("DELETE FROM korisnici WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Korisnik je obrisan.'];
    }

    public function getAll(): array {
        $result = $this->db->query("SELECT id, ime, prezime, email, uloga, aktivan, created_at FROM korisnici ORDER BY ime");
        $lista = [];
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
        return $lista;
    }

    public static function provjeriSesiju(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['korisnik_id'])) {
            header('Location: /skladiste/index.php?page=prijava');
            exit;
        }
    }

    public static function provjeriAdmin(): void {
        self::provjeriSesiju();
        if ($_SESSION['korisnik_uloga'] !== 'admin') {
            header('Location: /skladiste/index.php?page=dashboard&greska=pristup');
            exit;
        }
    }
}
