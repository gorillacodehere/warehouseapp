<?php
// classes/Kategorija.php

require_once __DIR__ . '/../config/database.php';

class Kategorija {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $result = $this->db->query(
            "SELECT k.*, COUNT(a.id) AS broj_artikala
             FROM kategorije k
             LEFT JOIN artikli a ON k.id=a.kategorija_id AND a.aktivan=1
             GROUP BY k.id ORDER BY k.naziv"
        );
        $lista = [];
        while ($row = $result->fetch_assoc()) $lista[] = $row;
        return $lista;
    }

    public function getById(int $id): ?array {
        $r = $this->db->query("SELECT * FROM kategorije WHERE id=$id LIMIT 1");
        return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
    }

    public function kreiraj(array $data): array {
        $naziv = $this->db->escape(trim($data['naziv']));
        $opis  = $this->db->escape(trim($data['opis'] ?? ''));
        if (empty($naziv)) return ['uspjeh' => false, 'poruka' => 'Naziv je obavezan.'];
        $this->db->query("INSERT INTO kategorije (naziv, opis) VALUES ('$naziv','$opis')");
        return ['uspjeh' => true, 'poruka' => 'Kategorija kreirana.'];
    }

    public function azuriraj(int $id, array $data): array {
        $naziv = $this->db->escape(trim($data['naziv']));
        $opis  = $this->db->escape(trim($data['opis'] ?? ''));
        $this->db->query("UPDATE kategorije SET naziv='$naziv', opis='$opis' WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Kategorija ažurirana.'];
    }

    public function obrisi(int $id): array {
        $check = $this->db->query("SELECT COUNT(*) AS n FROM artikli WHERE kategorija_id=$id AND aktivan=1");
        if ($check->fetch_assoc()['n'] > 0) {
            return ['uspjeh' => false, 'poruka' => 'Ne možete obrisati kategoriju koja ima artikle.'];
        }
        $this->db->query("DELETE FROM kategorije WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Kategorija obrisana.'];
    }
}


// ── Dobavljac ───────────────────────────────────────────────────────────────

class Dobavljac {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $result = $this->db->query(
            "SELECT d.*, COUNT(a.id) AS broj_artikala
             FROM dobavljaci d
             LEFT JOIN artikli a ON d.id=a.dobavljac_id AND a.aktivan=1
             WHERE d.aktivan=1
             GROUP BY d.id ORDER BY d.naziv"
        );
        $lista = [];
        while ($row = $result->fetch_assoc()) $lista[] = $row;
        return $lista;
    }

    public function getById(int $id): ?array {
        $r = $this->db->query("SELECT * FROM dobavljaci WHERE id=$id LIMIT 1");
        return ($r && $r->num_rows) ? $r->fetch_assoc() : null;
    }

    public function kreiraj(array $data): array {
        $naziv   = $this->db->escape(trim($data['naziv']));
        $kontakt = $this->db->escape(trim($data['kontakt_osoba'] ?? ''));
        $email   = $this->db->escape(trim($data['email'] ?? ''));
        $tel     = $this->db->escape(trim($data['telefon'] ?? ''));
        $adresa  = $this->db->escape(trim($data['adresa'] ?? ''));
        if (empty($naziv)) return ['uspjeh' => false, 'poruka' => 'Naziv je obavezan.'];
        $this->db->query("INSERT INTO dobavljaci (naziv, kontakt_osoba, email, telefon, adresa)
                          VALUES ('$naziv','$kontakt','$email','$tel','$adresa')");
        return ['uspjeh' => true, 'poruka' => 'Dobavljač kreiran.'];
    }

    public function azuriraj(int $id, array $data): array {
        $naziv   = $this->db->escape(trim($data['naziv']));
        $kontakt = $this->db->escape(trim($data['kontakt_osoba'] ?? ''));
        $email   = $this->db->escape(trim($data['email'] ?? ''));
        $tel     = $this->db->escape(trim($data['telefon'] ?? ''));
        $adresa  = $this->db->escape(trim($data['adresa'] ?? ''));
        $this->db->query("UPDATE dobavljaci SET naziv='$naziv', kontakt_osoba='$kontakt',
                          email='$email', telefon='$tel', adresa='$adresa' WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Dobavljač ažuriran.'];
    }

    public function obrisi(int $id): array {
        $this->db->query("UPDATE dobavljaci SET aktivan=0 WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Dobavljač obrisan.'];
    }
}
