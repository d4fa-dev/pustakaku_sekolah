-- =========================================================
-- PUSTAKAKU
-- Database Perpustakaan
-- =========================================================

CREATE DATABASE IF NOT EXISTS pustakaku
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pustakaku;


-- =========================================================
-- TABLE: users
-- =========================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    nama VARCHAR(100) NOT NULL,

    role ENUM('admin', 'petugas')
        NOT NULL DEFAULT 'petugas',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- TABLE: kategori
-- =========================================================

CREATE TABLE kategori (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nama_kategori VARCHAR(100) NOT NULL UNIQUE,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- TABLE: anggota
-- =========================================================

CREATE TABLE anggota (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    kode_anggota VARCHAR(30) NOT NULL UNIQUE,

    nama VARCHAR(100) NOT NULL,

    jenis_kelamin ENUM('L', 'P') NOT NULL,

    kelas VARCHAR(50) DEFAULT NULL,

    alamat TEXT DEFAULT NULL,

    no_hp VARCHAR(20) DEFAULT NULL,

    status ENUM('Aktif', 'Tidak Aktif')
        NOT NULL DEFAULT 'Aktif',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- TABLE: buku
-- =========================================================

CREATE TABLE buku (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    kode_buku VARCHAR(30) NOT NULL UNIQUE,

    isbn VARCHAR(30) DEFAULT NULL,

    judul VARCHAR(255) NOT NULL,

    penulis VARCHAR(150) DEFAULT NULL,

    penerbit VARCHAR(150) DEFAULT NULL,

    tahun_terbit YEAR DEFAULT NULL,

    kategori_id INT UNSIGNED DEFAULT NULL,

    stok INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_buku_kategori
        FOREIGN KEY (kategori_id)
        REFERENCES kategori(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_buku_stok
        CHECK (stok >= 0)
);


-- =========================================================
-- TABLE: peminjaman
-- =========================================================

CREATE TABLE peminjaman (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    kode_peminjaman VARCHAR(50) NOT NULL UNIQUE,

    anggota_id INT UNSIGNED NOT NULL,

    buku_id INT UNSIGNED NOT NULL,

    user_id INT UNSIGNED DEFAULT NULL,

    tanggal_pinjam DATE NOT NULL,

    tanggal_jatuh_tempo DATE NOT NULL,

    tanggal_kembali DATE DEFAULT NULL,

    status ENUM(
        'dipinjam',
        'dikembalikan',
        'terlambat'
    ) NOT NULL DEFAULT 'dipinjam',

    denda DECIMAL(12,2)
        NOT NULL DEFAULT 0,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_peminjaman_anggota
        FOREIGN KEY (anggota_id)
        REFERENCES anggota(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_peminjaman_buku
        FOREIGN KEY (buku_id)
        REFERENCES buku(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_peminjaman_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_peminjaman_denda
        CHECK (denda >= 0)
);


-- =========================================================
-- INDEX
-- =========================================================

CREATE INDEX idx_peminjaman_tanggal_pinjam
    ON peminjaman(tanggal_pinjam);

CREATE INDEX idx_peminjaman_tanggal_jatuh_tempo
    ON peminjaman(tanggal_jatuh_tempo);

CREATE INDEX idx_peminjaman_status
    ON peminjaman(status);

CREATE INDEX idx_peminjaman_anggota
    ON peminjaman(anggota_id);

CREATE INDEX idx_peminjaman_buku
    ON peminjaman(buku_id);


-- =========================================================
-- DATA AWAL
-- =========================================================

-- Password di bawah adalah hasil password_hash()
-- untuk password: password

INSERT INTO users (
    username,
    password,
    nama,
    role
) VALUES (
    'admin',
    MD5('admin123'),
    'Administrator',
    'admin'
);


-- =========================================================
-- DATA KATEGORI
-- =========================================================

INSERT INTO kategori
(nama_kategori)
VALUES
('Fiksi'),
('Non-Fiksi'),
('Pelajaran'),
('Teknologi'),
('Sejarah');


-- =========================================================
-- SELESAI
-- =========================================================
