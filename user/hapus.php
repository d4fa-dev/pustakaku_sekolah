<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibAdmin();

$user = userLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

if ($id === (int) $user['id']) {
    header("Location: index.php?error=self_delete");
    exit;
}

$cek = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $cek,
    "i",
    $id
);

mysqli_stmt_execute($cek);

$result = mysqli_stmt_get_result($cek);

$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php?error=not_found");
    exit;
}

$hapus = mysqli_prepare(
    $koneksi,
    "DELETE FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $hapus,
    "i",
    $id
);

if (mysqli_stmt_execute($hapus)) {

    header("Location: index.php?success=deleted");
    exit;

} else {

    header("Location: index.php?error=delete_failed");
    exit;
}