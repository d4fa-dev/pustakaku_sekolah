<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?error=not_found");
    exit;
}

mysqli_begin_transaction($koneksi);

try {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            id,
            buku_id,
            tanggal_jatuh_tempo,
            status
         FROM peminjaman
         WHERE id = ?
         LIMIT 1
         FOR UPDATE"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $peminjaman = mysqli_fetch_assoc($result);

    if (!$peminjaman) {

        mysqli_rollback($koneksi);

        header("Location: index.php?error=not_found");
        exit;
    }

    if (
        $peminjaman['status'] === 'dikembalikan'
    ) {

        mysqli_rollback($koneksi);

        header("Location: index.php?error=already_returned");
        exit;
    }


    $tanggalKembali = date('Y-m-d');

    $tanggalJatuhTempo =
        $peminjaman['tanggal_jatuh_tempo'];


    $denda = 0;

    if (
        $tanggalJatuhTempo !== null &&
        $tanggalJatuhTempo !== '' &&
        strtotime($tanggalKembali) >
        strtotime($tanggalJatuhTempo)
    ) {

        $selisihHari =
            floor(
                (
                    strtotime($tanggalKembali) -
                    strtotime($tanggalJatuhTempo)
                ) / 86400
            );

        $tarifDendaPerHari = 1000;

        $denda =
            $selisihHari *
            $tarifDendaPerHari;
    }


    $updatePeminjaman = mysqli_prepare(
        $koneksi,
        "UPDATE peminjaman
         SET tanggal_kembali = ?,
             status = 'dikembalikan',
             denda = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $updatePeminjaman,
        "sdi",
        $tanggalKembali,
        $denda,
        $id
    );

    if (
        !mysqli_stmt_execute(
            $updatePeminjaman
        )
    ) {

        throw new Exception(
            "Gagal memperbarui peminjaman."
        );
    }


    if (
        !empty($peminjaman['buku_id'])
    ) {

        $updateBuku = mysqli_prepare(
            $koneksi,
            "UPDATE buku
             SET stok = stok + 1
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $updateBuku,
            "i",
            $peminjaman['buku_id']
        );

        if (
            !mysqli_stmt_execute(
                $updateBuku
            )
        ) {

            throw new Exception(
                "Gagal mengembalikan stok buku."
            );
        }
    }


    mysqli_commit($koneksi);

    header(
        "Location: index.php?success=returned"
    );

    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    header(
        "Location: index.php?error=process_failed"
    );

    exit;
}