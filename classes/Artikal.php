<?php
// classes/Artikal.php

require_once __DIR__ . '/../config/database.php';

class Artikal {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Svi artikli sa pretragom i filterom ──────────
    public function getAll(array $filter = []): array {
        $where = ['a.aktivan=1'];

        if (!empty($filter['search'])) {
            $s = $this->db->escape($filter['search']);
            $where[] = "(a.naziv LIKE '%$s%' OR a.sku LIKE '%$s%' OR a.lokacija LIKE '%$s%')";
        }
        if (!empty($filter['kategorija_id'])) {
            $where[] = 'a.kategorija_id=' . (int)$filter['kategorija_id'];
        }
        if (!empty($filter['dobavljac_id'])) {
            $where[] = 'a.dobavljac_id=' . (int)$filter['dobavljac_id'];
        }
        if (isset($filter['nizak_stock']) && $filter['nizak_stock']) {
            $where[] = 'a.kolicina <= a.min_kolicina';
        }

        $whereStr = implode(' AND ', $where);
        $sql = "SELECT a.*, k.naziv AS kategorija, d.naziv AS dobavljac
                FROM artikli a
                LEFT JOIN kategorije k ON a.kategorija_id = k.id
                LEFT JOIN dobavljaci d ON a.dobavljac_id = d.id
                WHERE $whereStr
                ORDER BY a.naziv ASC";

        $result = $this->db->query($sql);
        $lista = [];
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
        return $lista;
    }

    public function getById(int $id): ?array {
        $result = $this->db->query(
            "SELECT a.*, k.naziv AS kategorija, d.naziv AS dobavljac
             FROM artikli a
             LEFT JOIN kategorije k ON a.kategorija_id = k.id
             LEFT JOIN dobavljaci d ON a.dobavljac_id = d.id
             WHERE a.id=$id LIMIT 1"
        );
        if ($result && $result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function kreiraj(array $data): array {
        $naziv       = $this->db->escape(trim($data['naziv']));
        $sku         = $this->db->escape(trim($data['sku'] ?? ''));
        $opis        = $this->db->escape(trim($data['opis'] ?? ''));
        $kat_id      = (int)($data['kategorija_id'] ?? 0);
        $dob_id      = (int)($data['dobavljac_id'] ?? 0);
        $kolicina    = (int)($data['kolicina'] ?? 0);
        $min_kol     = (int)($data['min_kolicina'] ?? 5);
        $cena_nab    = (float)($data['cena_nabavke'] ?? 0);
        $cena_prod   = (float)($data['cena_prodaje'] ?? 0);
        $jed         = $this->db->escape($data['jedinica_mere'] ?? 'kom');
        $lok         = $this->db->escape($data['lokacija'] ?? '');

        if (empty($naziv)) return ['uspjeh' => false, 'poruka' => 'Naziv je obavezan.'];

        $sql = "INSERT INTO artikli (naziv, sku, opis, kategorija_id, dobavljac_id, kolicina,
                min_kolicina, cena_nabavke, cena_prodaje, jedinica_mere, lokacija)
                VALUES ('$naziv','$sku','$opis',
                " . ($kat_id ?: 'NULL') . "," . ($dob_id ?: 'NULL') . ",
                $kolicina,$min_kol,$cena_nab,$cena_prod,'$jed','$lok')";

        // Provjeri duplikat SKU
        if (!empty($sku)) {
            $check = $this->db->query("SELECT id FROM artikli WHERE sku='$sku' AND aktivan=1");
            if ($check && $check->num_rows > 0) {
                return ['uspjeh' => false, 'poruka' => "SKU '$sku' već postoji. Unesite drugi SKU."];
            }
        }

        try {
            if ($this->db->query($sql)) {
                $id = $this->db->getLastId();
                if ($kolicina > 0) {
                    $uid = (int)$_SESSION['korisnik_id'];
                    $this->db->query("INSERT INTO transakcije (artikal_id, korisnik_id, tip, kolicina, napomena)
                                      VALUES ($id, $uid, 'ulaz', $kolicina, 'Početno stanje')");
                }
                return ['uspjeh' => true, 'poruka' => 'Artikal je kreiran.', 'id' => $id];
            }
            return ['uspjeh' => false, 'poruka' => 'Greška pri kreiranju.'];
        } catch (Exception $e) {
            return ['uspjeh' => false, 'poruka' => 'Greška: ' . $e->getMessage()];
        }
    }

    public function azuriraj(int $id, array $data): array {
        $naziv     = $this->db->escape(trim($data['naziv']));
        $sku       = $this->db->escape(trim($data['sku'] ?? ''));
        $opis      = $this->db->escape(trim($data['opis'] ?? ''));
        $kat_id    = (int)($data['kategorija_id'] ?? 0);
        $dob_id    = (int)($data['dobavljac_id'] ?? 0);
        $min_kol   = (int)($data['min_kolicina'] ?? 5);
        $cena_nab  = (float)($data['cena_nabavke'] ?? 0);
        $cena_prod = (float)($data['cena_prodaje'] ?? 0);
        $jed       = $this->db->escape($data['jedinica_mere'] ?? 'kom');
        $lok       = $this->db->escape($data['lokacija'] ?? '');

        $sql = "UPDATE artikli SET naziv='$naziv', sku='$sku', opis='$opis',
                kategorija_id=" . ($kat_id ?: 'NULL') . ",
                dobavljac_id=" . ($dob_id ?: 'NULL') . ",
                min_kolicina=$min_kol, cena_nabavke=$cena_nab, cena_prodaje=$cena_prod,
                jedinica_mere='$jed', lokacija='$lok'
                WHERE id=$id";

        if ($this->db->query($sql)) {
            return ['uspjeh' => true, 'poruka' => 'Artikal je ažuriran.'];
        }
        return ['uspjeh' => false, 'poruka' => 'Greška pri ažuriranju.'];
    }

    public function obrisi(int $id): array {
        $this->db->query("UPDATE artikli SET aktivan=0 WHERE id=$id");
        return ['uspjeh' => true, 'poruka' => 'Artikal je obrisan.'];
    }

    // ── Ulaz / Izlaz kolicine ────────────────────────
    public function promeniKolicinu(int $id, int $kolicina, string $tip, string $napomena = ''): array {
        $artikal = $this->getById($id);
        if (!$artikal) return ['uspjeh' => false, 'poruka' => 'Artikal nije pronađen.'];

        $nova = (int)$artikal['kolicina'];
        if ($tip === 'ulaz') {
            $nova += $kolicina;
        } elseif ($tip === 'izlaz') {
            if ($nova < $kolicina) return ['uspjeh' => false, 'poruka' => 'Nedovoljno na stanju.'];
            $nova -= $kolicina;
        } else {
            $nova = $kolicina; // korekcija
        }

        $nap = $this->db->escape($napomena);
        $uid = (int)$_SESSION['korisnik_id'];
        $this->db->query("UPDATE artikli SET kolicina=$nova WHERE id=$id");
        $this->db->query("INSERT INTO transakcije (artikal_id, korisnik_id, tip, kolicina, napomena)
                          VALUES ($id, $uid, '$tip', $kolicina, '$nap')");

        return ['uspjeh' => true, 'poruka' => 'Kolicina je ažurirana.', 'nova_kolicina' => $nova];
    }

    // ── Statistika ───────────────────────────────────
    public function getStatistika(): array {
        $ukupno  = $this->db->query("SELECT COUNT(*) AS n FROM artikli WHERE aktivan=1")->fetch_assoc()['n'];
        $nizak   = $this->db->query("SELECT COUNT(*) AS n FROM artikli WHERE aktivan=1 AND kolicina<=min_kolicina")->fetch_assoc()['n'];
        $vrednost = $this->db->query("SELECT SUM(kolicina*cena_nabavke) AS v FROM artikli WHERE aktivan=1")->fetch_assoc()['v'];
        $kategorije = $this->db->query("SELECT COUNT(*) AS n FROM kategorije")->fetch_assoc()['n'];
        return [
            'ukupno_artikala' => (int)$ukupno,
            'nizak_stock'     => (int)$nizak,
            'vrednost_stanja' => (float)($vrednost ?? 0),
            'kategorije'      => (int)$kategorije,
        ];
    }

    public function getTransakcije(int $artikal_id = 0, int $limit = 50): array {
        $where = $artikal_id ? "WHERE t.artikal_id=$artikal_id" : '';
        $result = $this->db->query(
            "SELECT t.*, a.naziv AS artikal, CONCAT(k.ime,' ',k.prezime) AS korisnik
             FROM transakcije t
             JOIN artikli a ON t.artikal_id=a.id
             JOIN korisnici k ON t.korisnik_id=k.id
             $where
             ORDER BY t.created_at DESC LIMIT $limit"
        );
        $lista = [];
        while ($row = $result->fetch_assoc()) $lista[] = $row;
        return $lista;
    }
}
