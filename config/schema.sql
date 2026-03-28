-- =============================================
-- SkladišteApp - Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS skladiste_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skladiste_db;

-- Korisnici
CREATE TABLE IF NOT EXISTS korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(100) NOT NULL,
    prezime VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    lozinka VARCHAR(255) NOT NULL,
    uloga ENUM('admin','korisnik') DEFAULT 'korisnik',
    aktivan TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Kategorije
CREATE TABLE IF NOT EXISTS kategorije (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(100) NOT NULL,
    opis TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dobavljaci
CREATE TABLE IF NOT EXISTS dobavljaci (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(150) NOT NULL,
    kontakt_osoba VARCHAR(100),
    email VARCHAR(150),
    telefon VARCHAR(30),
    adresa TEXT,
    aktivan TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Artikli
CREATE TABLE IF NOT EXISTS artikli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(200) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    opis TEXT,
    kategorija_id INT,
    dobavljac_id INT,
    kolicina INT DEFAULT 0,
    min_kolicina INT DEFAULT 5,
    cena_nabavke DECIMAL(10,2) DEFAULT 0.00,
    cena_prodaje DECIMAL(10,2) DEFAULT 0.00,
    jedinica_mere VARCHAR(20) DEFAULT 'kom',
    lokacija VARCHAR(100),
    aktivan TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategorija_id) REFERENCES kategorije(id) ON DELETE SET NULL,
    FOREIGN KEY (dobavljac_id) REFERENCES dobavljaci(id) ON DELETE SET NULL
);

-- Transakcije (ulaz/izlaz)
CREATE TABLE IF NOT EXISTS transakcije (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artikal_id INT NOT NULL,
    korisnik_id INT NOT NULL,
    tip ENUM('ulaz','izlaz','korekcija') NOT NULL,
    kolicina INT NOT NULL,
    napomena TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artikal_id) REFERENCES artikli(id) ON DELETE CASCADE,
    FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE
);

-- =============================================
-- Početni podaci
-- =============================================

-- Admin korisnik (lozinka: admin123)
INSERT INTO korisnici (ime, prezime, email, lozinka, uloga) VALUES
('Admin', 'Sistem', 'admin@skladiste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Petar', 'Petrović', 'petar@skladiste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'korisnik');

-- Kategorije
INSERT INTO kategorije (naziv, opis) VALUES
('Elektronika', 'Elektronske komponente i uređaji'),
('Alati', 'Ručni i električni alati'),
('Kancelarijski materijal', 'Papir, olovke, fascikle i ostalo'),
('Ambalaža', 'Kutije, folije i materijal za pakovanje'),
('Rezervni delovi', 'Mašinski i drugi rezervni delovi');

-- Dobavljaci
INSERT INTO dobavljaci (naziv, kontakt_osoba, email, telefon, adresa) VALUES
('TechSupply d.o.o.', 'Marko Jovanović', 'marko@techsupply.rs', '011-123-4567', 'Bulevar Oslobođenja 15, Beograd'),
('Alati Pro', 'Nikola Nikolić', 'nikola@alatipro.rs', '021-987-6543', 'Zmaj Jovina 5, Novi Sad'),
('PaperWorld', 'Ana Anić', 'ana@paperworld.rs', '018-555-0101', 'Niška 22, Niš');

-- Artikli
INSERT INTO artikli (naziv, sku, opis, kategorija_id, dobavljac_id, kolicina, min_kolicina, cena_nabavke, cena_prodaje, jedinica_mere, lokacija) VALUES
('Laptop Dell Latitude 5540', 'EL-001', '15.6" FHD, i5-1345U, 16GB RAM, 512GB SSD', 1, 1, 12, 3, 85000.00, 105000.00, 'kom', 'Polica A1'),
('Bežični miš Logitech MX3', 'EL-002', 'Ergonomski, Bluetooth + USB', 1, 1, 45, 10, 4500.00, 6500.00, 'kom', 'Polica A2'),
('Električna bušilica Bosch', 'AL-001', '750W, 13mm stezna glava', 2, 2, 8, 2, 12000.00, 16500.00, 'kom', 'Polica B1'),
('Metrični ključevi set 8-19mm', 'AL-002', 'Set od 8 ključeva, hromovani čelik', 2, 2, 20, 5, 2800.00, 4200.00, 'set', 'Polica B2'),
('Kancelarijski papir A4 500l', 'KM-001', '80g/m², bijeli, 500 listova', 3, 3, 200, 50, 350.00, 550.00, 'riz', 'Polica C1'),
('Fascikla PVC A4', 'KM-002', 'Providna PVC fascikla sa mehanizmom', 3, 3, 500, 100, 85.00, 150.00, 'kom', 'Polica C2'),
('Kartonska kutija 40x30x20', 'AM-001', 'Dvoslojna kartonska kutija', 4, 3, 300, 50, 65.00, 120.00, 'kom', 'Polica D1'),
('Ležaj 6205 2RS', 'RD-001', 'Kuglični ležaj, zatvoreni', 5, 1, 3, 10, 850.00, 1400.00, 'kom', 'Polica E1');

-- Transakcije
INSERT INTO transakcije (artikal_id, korisnik_id, tip, kolicina, napomena) VALUES
(1, 1, 'ulaz', 15, 'Početno stanje'),
(1, 1, 'izlaz', 3, 'Isporuka klijentu br. 1001'),
(5, 1, 'ulaz', 200, 'Kupovina kod dobavljaca'),
(8, 1, 'ulaz', 3, 'Prenos iz drugog skladišta');
