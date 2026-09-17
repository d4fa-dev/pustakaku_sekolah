<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah anggota memiliki riwayat peminjaman
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM peminjaman
     WHERE anggota_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

$totalPeminjaman = (int) ($data['total'] ?? 0);

/*
|--------------------------------------------------------------------------
| Jangan hapus anggota yang memiliki riwayat peminjaman
|--------------------------------------------------------------------------
*/

if ($totalPeminjaman > 0) {

    header("Location: index.php?error=has_peminjaman");
    exit;
}

/*
|--------------------------------------------------------------------------
| Pastikan anggota tersedia
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM anggota
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hapus anggota
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM anggota
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php?success=deleted");
    exit;
}

header("Location: index.php?error=delete");
exit;
