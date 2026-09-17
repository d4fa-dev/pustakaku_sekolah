<?php

require_once "../config/database.php";

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Cek apakah buku ada
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM buku WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) === 0) {

    header("Location: index.php?error=notfound");
    exit;
}


/*
|--------------------------------------------------------------------------
| Hapus buku
|--------------------------------------------------------------------------
*/

$hapus = mysqli_prepare(
    $koneksi,
    "DELETE FROM buku WHERE id = ?"
);

mysqli_stmt_bind_param(
    $hapus,
    "i",
    $id
);


if (mysqli_stmt_execute($hapus)) {

    header("Location: index.php?success=deleted");
    exit;

}


header("Location: index.php?error=delete");
exit;