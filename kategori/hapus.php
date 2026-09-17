<?php

require_once "../config/database.php";

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM kategori WHERE id = ?"
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