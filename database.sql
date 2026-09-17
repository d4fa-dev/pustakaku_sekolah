-- ============================================================
-- PUSTAKAKU
-- CLEAN DATABASE
-- MariaDB 10.4+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `pustakaku`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `pustakaku`;

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- ============================================================
-- HAPUS TABEL LAMA
-- ============================================================

DROP TABLE IF EXISTS `peminjaman`;
DROP TABLE IF EXISTS `buku`;
DROP TABLE IF EXISTS `anggota`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `users`;

-- ============================================================
-- TABLE USERS
-- ============================================================

CREATE TABLE `users` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE KATEGORI
-- ============================================================

CREATE TABLE `kategori` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_kategori` varchar(100) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

    PRIMARY KEY (`id`),
    UNIQUE KEY `nama_kategori` (`nama_kategori`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE BUKU
-- ============================================================

CREATE TABLE `buku` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_buku` varchar(30) NOT NULL,
    `isbn` varchar(30) DEFAULT NULL,
    `judul` varchar(255) NOT NULL,
    `penulis` varchar(150) DEFAULT NULL,
    `penerbit` varchar(150) DEFAULT NULL,
    `tahun_terbit` year(4) DEFAULT NULL,
    `kategori_id` int(10) UNSIGNED DEFAULT NULL,
    `stok` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_buku` (`kode_buku`),
    KEY `fk_buku_kategori` (`kategori_id`),

    CONSTRAINT `fk_buku_kategori`
        FOREIGN KEY (`kategori_id`)
        REFERENCES `kategori` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE ANGGOTA
-- ============================================================

CREATE TABLE `anggota` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nisn` varchar(20) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `jenis_kelamin` enum('L','P') NOT NULL,
    `kelas` varchar(50) DEFAULT NULL,
    `no_hp` varchar(20) DEFAULT NULL,
    `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_anggota` (`nisn`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE PEMINJAMAN
-- ============================================================

CREATE TABLE `peminjaman` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_peminjaman` varchar(50) NOT NULL,
    `anggota_id` int(10) UNSIGNED NOT NULL,
    `buku_id` int(10) UNSIGNED NOT NULL,
    `user_id` int(10) UNSIGNED DEFAULT NULL,
    `tanggal_pinjam` date NOT NULL,
    `tanggal_jatuh_tempo` date NOT NULL,
    `tanggal_kembali` date DEFAULT NULL,
    `status` enum(
        'dipinjam',
        'dikembalikan',
        'terlambat'
    ) NOT NULL DEFAULT 'dipinjam',
    `denda` decimal(12,2) NOT NULL DEFAULT 0.00,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

    PRIMARY KEY (`id`),

    UNIQUE KEY `kode_peminjaman` (`kode_peminjaman`),

    KEY `fk_peminjaman_user` (`user_id`),
    KEY `idx_peminjaman_tanggal_pinjam` (`tanggal_pinjam`),
    KEY `idx_peminjaman_tanggal_jatuh_tempo` (`tanggal_jatuh_tempo`),
    KEY `idx_peminjaman_status` (`status`),
    KEY `idx_peminjaman_anggota` (`anggota_id`),
    KEY `idx_peminjaman_buku` (`buku_id`),

    CONSTRAINT `fk_peminjaman_anggota`
        FOREIGN KEY (`anggota_id`)
        REFERENCES `anggota` (`id`)
        ON UPDATE CASCADE,

    CONSTRAINT `fk_peminjaman_buku`
        FOREIGN KEY (`buku_id`)
        REFERENCES `buku` (`id`)
        ON UPDATE CASCADE,

    CONSTRAINT `fk_peminjaman_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ADMIN AWAL
-- ============================================================
-- username : admin
-- password : 123456
--
-- Hash ini mengikuti sistem login lama Pustakaku.

INSERT INTO `users`
    (`id`, `username`, `password`, `nama`, `role`)
VALUES
    (
        1,
        'admin',
        '0192023a7bbd73250516f069df18b500',
        'Administrator',
        'admin'
    );

-- ============================================================
-- AUTO_INCREMENT
-- ============================================================

ALTER TABLE `users`
    AUTO_INCREMENT = 2;

ALTER TABLE `kategori`
    AUTO_INCREMENT = 1;

ALTER TABLE `buku`
    AUTO_INCREMENT = 1;

ALTER TABLE `anggota`
    AUTO_INCREMENT = 1;

ALTER TABLE `peminjaman`
    AUTO_INCREMENT = 1;

-- ============================================================
-- SELESAI
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;