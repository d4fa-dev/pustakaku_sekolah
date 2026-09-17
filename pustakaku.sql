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
-- AUTO_INCREMENT-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Sep 17, 2026 at 03:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pustakaku`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` int(10) UNSIGNED NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `nisn`, `nama`, `jenis_kelamin`, `kelas`, `no_hp`, `status`, `created_at`) VALUES
(1, '0067812345', 'Ahmad Fauzan', 'L', 'X PPLG 1', '081234560001', 'Aktif', '2026-09-17 01:23:22'),
(2, '0067812346', 'Muhammad Rizky', 'L', 'X PPLG 1', '081234560002', 'Aktif', '2026-09-17 01:23:22'),
(3, '0067812347', 'Fajar Ramadhan', 'L', 'X PPLG 1', '081234560003', 'Aktif', '2026-09-17 01:23:22'),
(4, '0067812348', 'Dimas Saputra', 'L', 'X PPLG 1', '081234560004', 'Aktif', '2026-09-17 01:23:22'),
(5, '0067812349', 'Rian Maulana', 'L', 'X PPLG 1', '081234560005', 'Aktif', '2026-09-17 01:23:22'),
(6, '0067812350', 'Siti Aisyah', 'P', 'X PPLG 1', '081234560006', 'Aktif', '2026-09-17 01:23:22'),
(7, '0067812351', 'Nabila Putri', 'P', 'X PPLG 1', '081234560007', 'Aktif', '2026-09-17 01:23:22'),
(8, '0067812352', 'Aulia Rahma', 'P', 'X PPLG 1', '081234560008', 'Aktif', '2026-09-17 01:23:22'),
(9, '0067812353', 'Nurul Hidayah', 'P', 'X PPLG 1', '081234560009', 'Aktif', '2026-09-17 01:23:22'),
(10, '0067812354', 'Putri Amelia', 'P', 'X PPLG 1', '081234560010', 'Aktif', '2026-09-17 01:23:22'),
(11, '0067812355', 'Bagas Pratama', 'L', 'X PPLG 2', '081234560011', 'Aktif', '2026-09-17 01:23:22'),
(12, '0067812356', 'Rafli Kurniawan', 'L', 'X PPLG 2', '081234560012', 'Aktif', '2026-09-17 01:23:22'),
(13, '0067812357', 'Ardiansyah', 'L', 'X PPLG 2', '081234560013', 'Aktif', '2026-09-17 01:23:22'),
(14, '0067812358', 'Ilham Saputra', 'L', 'X PPLG 2', '081234560014', 'Aktif', '2026-09-17 01:23:22'),
(15, '0067812359', 'Reza Firmansyah', 'L', 'X PPLG 2', '081234560015', 'Aktif', '2026-09-17 01:23:22'),
(16, '0067812360', 'Maya Sari', 'P', 'X PPLG 2', '081234560016', 'Aktif', '2026-09-17 01:23:22'),
(17, '0067812361', 'Dewi Lestari', 'P', 'X PPLG 2', '081234560017', 'Aktif', '2026-09-17 01:23:22'),
(18, '0067812362', 'Citra Maharani', 'P', 'X PPLG 2', '081234560018', 'Aktif', '2026-09-17 01:23:22'),
(19, '0067812363', 'Intan Permata', 'P', 'X PPLG 2', '081234560019', 'Aktif', '2026-09-17 01:23:22'),
(20, '0067812364', 'Vina Oktaviani', 'P', 'X PPLG 2', '081234560020', 'Aktif', '2026-09-17 01:23:22'),
(21, '0067812365', 'Andika Wijaya', 'L', 'XI PPLG 1', '081234560021', 'Aktif', '2026-09-17 01:23:22'),
(22, '0067812366', 'Bima Aditya', 'L', 'XI PPLG 1', '081234560022', 'Aktif', '2026-09-17 01:23:22'),
(23, '0067812367', 'Yoga Pratama', 'L', 'XI PPLG 1', '081234560023', 'Aktif', '2026-09-17 01:23:22'),
(24, '0067812368', 'Rizal Akbar', 'L', 'XI PPLG 1', '081234560024', 'Aktif', '2026-09-17 01:23:22'),
(25, '0067812369', 'Hendra Gunawan', 'L', 'XI PPLG 1', '081234560025', 'Aktif', '2026-09-17 01:23:22'),
(26, '0067812370', 'Fitria Ningsih', 'P', 'XI PPLG 1', '081234560026', 'Aktif', '2026-09-17 01:23:22'),
(27, '0067812371', 'Lia Anggraini', 'P', 'XI PPLG 1', '081234560027', 'Aktif', '2026-09-17 01:23:22'),
(28, '0067812372', 'Rahmawati', 'P', 'XI PPLG 1', '081234560028', 'Aktif', '2026-09-17 01:23:22'),
(29, '0067812373', 'Anisa Safitri', 'P', 'XI PPLG 1', '081234560029', 'Aktif', '2026-09-17 01:23:22'),
(30, '0067812374', 'Tiara Maharani', 'P', 'XI PPLG 1', '081234560030', 'Aktif', '2026-09-17 01:23:22'),
(31, '0067812375', 'Rangga Saputra', 'L', 'XII PPLG 1', '081234560031', 'Aktif', '2026-09-17 01:23:22'),
(32, '0067812376', 'Fikri Hidayat', 'L', 'XII PPLG 1', '081234560032', 'Aktif', '2026-09-17 01:23:22'),
(33, '0067812377', 'Aldi Kurniawan', 'L', 'XII PPLG 1', '081234560033', 'Aktif', '2026-09-17 01:23:22'),
(34, '0067812378', 'Doni Setiawan', 'L', 'XII PPLG 1', '081234560034', 'Aktif', '2026-09-17 01:23:22'),
(35, '0067812379', 'Raka Firmansyah', 'L', 'XII PPLG 1', '081234560035', 'Aktif', '2026-09-17 01:23:22'),
(36, '0067812380', 'Bella Aprilia', 'P', 'XII PPLG 1', '081234560036', 'Aktif', '2026-09-17 01:23:22'),
(37, '0067812381', 'Nia Ramadhani', 'P', 'XII PPLG 1', '081234560037', 'Aktif', '2026-09-17 01:23:22'),
(38, '0067812382', 'Sarah Amelia', 'P', 'XII PPLG 1', '081234560038', 'Aktif', '2026-09-17 01:23:22'),
(39, '0067812383', 'Ayu Lestari', 'P', 'XII PPLG 1', '081234560039', 'Aktif', '2026-09-17 01:23:22'),
(40, '0067812384', 'Melinda Putri', 'P', 'XII PPLG 1', '081234560040', 'Tidak Aktif', '2026-09-17 01:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_buku` varchar(30) NOT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(150) DEFAULT NULL,
  `penerbit` varchar(150) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `kategori_id` int(10) UNSIGNED DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `kode_buku`, `isbn`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `kategori_id`, `stok`, `created_at`) VALUES
(1, 'BK001', '9786026231231', 'Pemrograman PHP untuk Pemula', 'Budi Raharjo', 'Informatika', '2023', 1, 8, '2026-09-17 01:22:39'),
(2, 'BK002', '9786026231248', 'Belajar PHP dan MySQL', 'Andi Pratama', 'Elex Media Komputindo', '2022', 1, 7, '2026-09-17 01:22:39'),
(3, 'BK003', '9786230012345', 'Pemrograman JavaScript Modern', 'Rizky Maulana', 'Informatika', '2024', 1, 6, '2026-09-17 01:22:39'),
(4, 'BK004', '9786020015678', 'Dasar-Dasar Python', 'Fajar Nugroho', 'Gramedia', '2023', 1, 9, '2026-09-17 01:22:39'),
(5, 'BK005', '9786234567890', 'Algoritma dan Pemrograman', 'Abdul Kadir', 'Andi Publisher', '2022', 1, 8, '2026-09-17 01:22:39'),
(6, 'BK006', '9786021112233', 'Pengantar Teknologi Informasi', 'Sutarman', 'Bumi Aksara', '2022', 2, 7, '2026-09-17 01:22:39'),
(7, 'BK007', '9786022223344', 'Sistem Informasi Dasar', 'Tata Sutabri', 'Andi Publisher', '2021', 2, 6, '2026-09-17 01:22:39'),
(8, 'BK008', '9786233334455', 'Teknologi Digital Masa Kini', 'Agus Mulyanto', 'Informatika', '2024', 2, 8, '2026-09-17 01:22:39'),
(9, 'BK009', '9786024445566', 'Dasar Komputer dan Teknologi', 'Wahana Komputer', 'Andi Publisher', '2023', 2, 9, '2026-09-17 01:22:39'),
(10, 'BK010', '9786025556677', 'Belajar MySQL untuk Pemula', 'Budi Raharjo', 'Informatika', '2023', 3, 7, '2026-09-17 01:22:39'),
(11, 'BK011', '9786026667788', 'Database Design', 'Haryanto', 'Elex Media Komputindo', '2022', 3, 6, '2026-09-17 01:22:39'),
(12, 'BK012', '9786237778899', 'SQL dan Database Management', 'Rudi Hartono', 'Gramedia', '2024', 3, 8, '2026-09-17 01:22:39'),
(13, 'BK013', '9786028889900', 'Administrasi Basis Data', 'Dwi Prasetyo', 'Informatika', '2021', 3, 5, '2026-09-17 01:22:39'),
(14, 'BK014', '9786021234567', 'Jaringan Komputer Dasar', 'Melwin Syafrizal', 'Andi Publisher', '2022', 4, 8, '2026-09-17 01:22:39'),
(15, 'BK015', '9786232345678', 'Cisco Networking Dasar', 'Agung Setiawan', 'Informatika', '2023', 4, 6, '2026-09-17 01:22:39'),
(16, 'BK016', '9786023456789', 'Administrasi Jaringan', 'Iwan Sofana', 'Informatika', '2022', 4, 7, '2026-09-17 01:22:39'),
(17, 'BK017', '9786234567812', 'Membangun Jaringan LAN', 'Wahana Komputer', 'Andi Publisher', '2021', 4, 5, '2026-09-17 01:22:39'),
(18, 'BK018', '9786025678123', 'Rekayasa Perangkat Lunak', 'Rosa A.S.', 'Informatika', '2023', 5, 9, '2026-09-17 01:22:39'),
(19, 'BK019', '9786236781234', 'UML dan Perancangan Sistem', 'M. Shalahuddin', 'Informatika', '2022', 5, 7, '2026-09-17 01:22:39'),
(20, 'BK020', '9786027891234', 'Analisis dan Perancangan Sistem', 'Jogiyanto', 'Andi Publisher', '2021', 5, 6, '2026-09-17 01:22:39'),
(21, 'BK021', '9786238912345', 'Web Development untuk SMK', 'Eko Kurniawan', 'Gramedia', '2024', 5, 8, '2026-09-17 01:22:39'),
(22, 'BK022', '9786029012345', 'Praktik Pemrograman Web', 'Dadan Ramdhani', 'Informatika', '2023', 5, 7, '2026-09-17 01:22:39'),
(23, 'BK023', '9786021122334', 'Desain Grafis untuk Pemula', 'Jubilee Enterprise', 'Elex Media', '2023', 6, 7, '2026-09-17 01:22:39'),
(24, 'BK024', '9786232233445', 'Adobe Photoshop Dasar', 'Wahana Komputer', 'Andi Publisher', '2022', 6, 6, '2026-09-17 01:22:39'),
(25, 'BK025', '9786023344556', 'Adobe Illustrator untuk Desainer', 'Madcoms', 'Andi Publisher', '2021', 6, 5, '2026-09-17 01:22:39'),
(26, 'BK026', '9786024455667', 'Matematika SMK Kelas X', 'Kemendikbud', 'Kemendikbud', '2022', 7, 10, '2026-09-17 01:22:39'),
(27, 'BK027', '9786025566778', 'Matematika SMK Kelas XI', 'Kemendikbud', 'Kemendikbud', '2023', 7, 9, '2026-09-17 01:22:39'),
(28, 'BK028', '9786236677889', 'Matematika SMK Kelas XII', 'Kemendikbud', 'Kemendikbud', '2024', 7, 8, '2026-09-17 01:22:39'),
(29, 'BK029', '9786027788990', 'Bahasa Indonesia untuk SMK', 'Kemendikbud', 'Kemendikbud', '2022', 8, 8, '2026-09-17 01:22:39'),
(30, 'BK030', '9786238899001', 'Mahir Berbahasa Indonesia', 'Yeti Mulyati', 'Erlangga', '2023', 8, 6, '2026-09-17 01:22:39'),
(31, 'BK031', '9786029900112', 'Keterampilan Menulis', 'Dalman', 'Rajawali Pers', '2021', 8, 5, '2026-09-17 01:22:39'),
(32, 'BK032', '9786021011121', 'English for Vocational School', 'John Eastwood', 'Oxford', '2022', 9, 7, '2026-09-17 01:22:39'),
(33, 'BK033', '9786231213141', 'Basic English Grammar', 'Betty Azar', 'Pearson', '2023', 9, 6, '2026-09-17 01:22:39'),
(34, 'BK034', '9786021516171', 'English Conversation', 'Jack C. Richards', 'Cambridge', '2021', 9, 5, '2026-09-17 01:22:39'),
(35, 'BK035', '9786021819202', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', '2020', 10, 6, '2026-09-17 01:22:39'),
(36, 'BK036', '9786022122232', 'Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', '2020', 10, 5, '2026-09-17 01:22:39'),
(37, 'BK037', '9786232324252', 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia', '2021', 10, 7, '2026-09-17 01:22:39'),
(38, 'BK038', '9786022627282', 'Bumi', 'Tere Liye', 'Gramedia', '2022', 10, 6, '2026-09-17 01:22:39'),
(39, 'BK039', '9786232930313', 'Bulan', 'Tere Liye', 'Gramedia', '2022', 10, 5, '2026-09-17 01:22:39'),
(40, 'BK040', '9786023233343', 'Hujan', 'Tere Liye', 'Gramedia', '2023', 10, 7, '2026-09-17 01:22:39');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `created_at`) VALUES
(1, 'Pemrograman', '2026-09-17 01:22:19'),
(2, 'Teknologi Informasi', '2026-09-17 01:22:19'),
(3, 'Basis Data', '2026-09-17 01:22:19'),
(4, 'Jaringan Komputer', '2026-09-17 01:22:19'),
(5, 'Rekayasa Perangkat Lunak', '2026-09-17 01:22:19'),
(6, 'Desain Grafis', '2026-09-17 01:22:19'),
(7, 'Matematika', '2026-09-17 01:22:19'),
(8, 'Bahasa Indonesia', '2026-09-17 01:22:19'),
(9, 'Bahasa Inggris', '2026-09-17 01:22:19'),
(10, 'Novel dan Fiksi', '2026-09-17 01:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_peminjaman` varchar(50) NOT NULL,
  `anggota_id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan','terlambat') NOT NULL DEFAULT 'dipinjam',
  `denda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `kode_peminjaman`, `anggota_id`, `buku_id`, `user_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `denda`, `created_at`) VALUES
(1, 'PJM-20260917033241-342', 1, 13, 1, '2026-09-17', '2026-09-24', '2026-09-17', 'dikembalikan', 0.00, '2026-09-17 01:32:41'),
(2, 'PJM-20260917033241-748', 1, 16, 1, '2026-09-17', '2026-09-24', '2026-09-17', 'dikembalikan', 0.00, '2026-09-17 01:32:41'),
(3, 'PJM-20260917033241-840', 1, 25, 1, '2026-09-17', '2026-09-24', '2026-09-17', 'dikembalikan', 0.00, '2026-09-17 01:32:41'),
(4, 'PJM-20260917033241-850', 1, 24, 1, '2026-09-17', '2026-09-24', '2026-09-17', 'dikembalikan', 0.00, '2026-09-17 01:32:41'),
(5, 'PJM-20260917033241-284', 1, 5, 1, '2026-09-17', '2026-09-24', '2026-09-17', 'dikembalikan', 0.00, '2026-09-17 01:32:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin', '2026-09-17 01:26:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_anggota` (`nisn`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`),
  ADD KEY `fk_buku_kategori` (`kategori_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_peminjaman` (`kode_peminjaman`),
  ADD KEY `fk_peminjaman_user` (`user_id`),
  ADD KEY `idx_peminjaman_tanggal_pinjam` (`tanggal_pinjam`),
  ADD KEY `idx_peminjaman_tanggal_jatuh_tempo` (`tanggal_jatuh_tempo`),
  ADD KEY `idx_peminjaman_status` (`status`),
  ADD KEY `idx_peminjaman_anggota` (`anggota_id`),
  ADD KEY `idx_peminjaman_buku` (`buku_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

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